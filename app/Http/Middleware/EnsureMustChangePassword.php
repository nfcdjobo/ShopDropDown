<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tant que must_change_password = true, l'utilisateur est redirigé vers
 * la page de changement de mot de passe — sauf sur les routes autorisées
 * (page elle-même, logout, endpoint de changement).
 *
 * À rattacher aux routes protégées du groupe customer/* et admin/*.
 */
class EnsureMustChangePassword
{
    /**
     * Routes nommées où l'utilisateur PEUT aller même avec must_change_password=true.
     */
    private const ALLOWED_ROUTES = [
        'password.change',       // GET  formulaire
        'password.change.store', // POST soumission
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $currentRoute = $request->route()?->getName();

            if (!in_array($currentRoute, self::ALLOWED_ROUTES, true)) {
                return redirect()
                    ->route('password.change')
                    ->with('warning', 'Pour votre sécurité, vous devez définir un nouveau mot de passe avant de continuer.');
            }
        }

        return $next($request);
    }
}
