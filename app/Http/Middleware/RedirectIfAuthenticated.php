<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Surcharge du middleware RedirectIfAuthenticated de Laravel.
 *
 * Utilisé sur les routes "guest" (login, forgot-password…).
 * Si l'utilisateur est déjà connecté :
 *   - admin / super_admin → dashboard admin
 *   - customer            → dashboard client
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                /**
                 * @var User $user
                 */
                $user = Auth::guard($guard)->user();

                // Redirection selon le rôle
                if ($user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->route('customer.dashboard');
            }
        }

        return $next($request);
    }
}
