<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;


class Authenticate extends Middleware
{
    /**
     * Retourne l'URL vers laquelle rediriger les utilisateurs non authentifiés.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Les requêtes AJAX / JSON retournent null → Laravel renvoie un 401
        if ($request->expectsJson()) {
            return null;
        }

        // Routes admin → page de connexion admin
        if ($request->routeIs('admin.*')) {
            return route('admin.login');
        }

        // Routes client (customer.*) → page de connexion admin également
        // car dans ce projet le login client se fait via le checkout
        return route('admin.login');
    }
}
