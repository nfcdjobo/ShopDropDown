<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Authentification dédiée au back-office.
 *
 * Séparé du flow checkout/client pour :
 *  - Avoir une URL de login propre (/admin/login) distincte de la boutique
 *  - Ajouter un rate-limiting agressif ciblé sur le back-office
 *  - Rediriger directement sur admin.dashboard après login
 *  - Gérer le "mot de passe oublié" côté admin sans impacter le flow client
 */
class AdminAuthController extends Controller
{
    // ────────────────────────────────────────────────────────────────
    //  Login
    // ────────────────────────────────────────────────────────────────

    /**
     * GET /admin/login
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    /**
     * POST /admin/login
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        // Rate-limiting : 5 tentatives / minute par email + IP
        $throttleKey = 'admin-login:' . strtolower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects.',
            ]);
        }

        // Vérification rôle APRÈS l'authentification pour éviter les énumérations
        $user = Auth::user();
        if (!$user->isAdmin() || !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'email' => 'Accès non autorisé.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        // Mettre à jour last_login_at
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()
            ->intended(route('admin.dashboard'))
            ->with('success', "Bienvenue, {$user->first_name} !");
    }

    /**
     * POST /admin/logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // ────────────────────────────────────────────────────────────────
    //  Mot de passe oublié
    // ────────────────────────────────────────────────────────────────

    /**
     * GET /admin/mot-de-passe-oublie
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * POST /admin/mot-de-passe-oublie — envoie le lien de réinitialisation.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:150'],
        ]);

        // Rate-limiting : 3 tentatives / 10 minutes
        $throttleKey = 'password-reset:' . strtolower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Trop de demandes. Réessayez dans {$seconds} secondes.",
            ]);
        }

        // Vérification discrète : on ne révèle pas si l'email n'existe pas
        $user = User::where('email', $request->input('email'))->first();
        if ($user && !$user->isAdmin()) {
            // L'email existe mais c'est un client : on ne répond pas différemment
            $user = null;
        }

        if ($user) {
            $status = Password::sendResetLink(
                $request->only('email'),
            );
        } else {
            $status = Password::RESET_LINK_SENT; // réponse identique
        }

        RateLimiter::hit($throttleKey, 600);

        return back()->with('status',
            'Si cet email correspond à un compte administrateur, un lien de réinitialisation a été envoyé.'
        );
    }

    /**
     * GET /admin/reinitialiser-mot-de-passe/{token}
     */
    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * POST /admin/reinitialiser-mot-de-passe — réinitialise le mot de passe.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'             => $password,
                    'must_change_password' => false,
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('admin.login')
                ->with('success', 'Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.');
        }

        return back()->withErrors([
            'email' => match ($status) {
                Password::INVALID_TOKEN => 'Le lien est invalide ou expiré.',
                Password::INVALID_USER  => 'Aucun compte trouvé avec cet email.',
                default                 => 'Impossible de réinitialiser le mot de passe.',
            },
        ]);
    }
}
