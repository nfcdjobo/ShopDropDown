<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Page de changement de mot de passe.
 *
 * Deux usages :
 *  1. Forcée : après le checkout invité, quand must_change_password = true.
 *     Le middleware EnsureMustChangePassword redirige ici tant que ce n'est pas fait.
 *  2. Volontaire : dans les paramètres du compte.
 */
class PasswordChangeController extends Controller
{
    /**
     * GET /mot-de-passe/changer
     */
    public function edit(Request $request): View
    {
        return view('auth.change-password', [
            'forced' => (bool) $request->user()?->must_change_password,
        ]);
    }

    /**
     * POST /mot-de-passe/changer
     */
    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'password'             => $request->validated('password'), // hashé via cast
            'must_change_password' => false,
        ])->save();

        // Regénérer l'ID de session par sécurité après un changement de MDP
        $request->session()->regenerate();

        return redirect()
            ->route('customer.dashboard')
            ->with('success', 'Votre mot de passe a été mis à jour avec succès.');
    }
}
