<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Authentification dédiée au back-office.
 *
 * Séparé du flow checkout/client pour :
 *  - Avoir une URL de login propre (/admin/login) distincte de la boutique
 *  - Ajouter un rate-limiting agressif ciblé sur le back-office
 *  - Rediriger directement sur admin.dashboard après login
 *  - Gérer le "mot de passe oublié" pour TOUS les comptes (clients et administrateurs) :
 *    les clients créés automatiquement au checkout reçoivent un mot de passe par défaut
 *    et doivent pouvoir le réinitialiser
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
        /**
         * @var User $user
         */
        $user = Auth::user();
        if (Auth::check() && $user->isAdmin()) {
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
        /**
         * @var User $user
         */
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
        $data  = $request->validate([
            'email' => ['required', 'string', 'email', 'max:150'],
        ]);
        $email = Str::lower(trim($data['email']));

        // Rate-limiting : 3 demandes / 10 minutes par email + IP
        $throttleKey = 'password-reset:' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Trop de demandes. Réessayez dans {$seconds} secondes.",
            ]);
        }

        // Réponse identique que le compte existe ou non : on ne révèle rien sur les comptes.
        $user = $this->findUserByEmail($email);

        if ($user && $user->is_active) {
            // On transmet l'email EXACT stocké en base : le jeton est lié à cette valeur.
            $status = Password::sendResetLink(['email' => $user->email]);

            if ($status !== Password::RESET_LINK_SENT) {
                // Ex. Password::RESET_THROTTLED (une demande < 60 s) : rien n'est envoyé.
                Log::warning('Réinitialisation mot de passe : lien non envoyé', [
                    'status'  => $status,
                    'user_id' => $user->id,
                ]);
            }
        }

        RateLimiter::hit($throttleKey, 600);

        return back()->with('status',
            'Si cet email correspond à un compte, un lien de réinitialisation vient d\'être envoyé.'
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
        $data = $request->validate([
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ], [
            'token.required'                 => 'Le lien de réinitialisation est incomplet. Redemandez-en un.',
            'email.required'                 => "L'adresse email est obligatoire.",
            'email.email'                    => "L'adresse email n'est pas valide.",
            'password.required'              => 'Le nouveau mot de passe est obligatoire.',
            'password.min'                   => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'             => 'Les deux mots de passe ne correspondent pas.',
            'password_confirmation.required' => 'Veuillez confirmer le mot de passe.',
        ]);

        // Limite les essais de jetons : 10 / minute par IP
        $throttleKey = 'admin-reset:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['email' => "Trop de tentatives. Réessayez dans {$seconds} secondes."]);
        }

        RateLimiter::hit($throttleKey, 60);

        // Email exact de la base (casse) ; si le compte est inconnu on garde la saisie → INVALID_USER
        $account = $this->findUserByEmail($data['email']);
        $email   = $account?->email ?? $data['email'];

        $resetUser = null; // compte réellement modifié (sert à choisir la page de connexion)

        $status = Password::reset(
            [
                'email'                 => $email,
                'password'              => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token'                 => $data['token'],
            ],
            function (User $user, string $password) use (&$resetUser) {
                $resetUser = $user;

                $user->forceFill([
                    'password'             => $password,           // haché par le cast 'hashed'
                    'must_change_password' => false,
                    'remember_token'       => Str::random(60),     // invalide les anciens « se souvenir de moi »
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            RateLimiter::clear($throttleKey);

            return redirect()
                ->to($this->loginUrlFor($resetUser))
                ->with('success', 'Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.');
        }

        // Aide au diagnostic (jamais le jeton ni le mot de passe dans les logs)
        Log::warning('Réinitialisation mot de passe : échec', ['status' => $status, 'email' => $email]);

        // Message générique : ne distingue pas « compte inconnu » de « jeton invalide »
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Le lien est invalide ou expiré. Redemandez un nouveau lien de réinitialisation.',
            ]);
    }

    /**
     * Retrouve un compte actif par email (client ou administrateur), sans tenir compte
     * de la casse. Les comptes supprimés (SoftDeletes) sont exclus automatiquement.
     */
    private function findUserByEmail(string $email): ?User
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [Str::lower(trim($email))])
            ->first();
    }

    /**
     * Page de connexion adaptée au rôle : back-office pour les admins, route « login »
     * (espace client) pour les autres, ou l'accueil si elle n'existe pas.
     */
    private function loginUrlFor(?User $user): string
    {
        if ($user?->isAdmin()) {
            return route('admin.login');
        }

        return Route::has('login') ? route('login') : url('/');
    }
}
