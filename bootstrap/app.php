<?php

// bootstrap/app.php

use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsureMustChangePassword;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'must.change.password' => EnsureMustChangePassword::class,
            'is.admin'             => EnsureIsAdmin::class,
        ]);

        // ↓ C'est ici que se règle le problème.
        // Laravel cherche la route 'login' quand auth échoue — elle n'existe pas.
        // On lui indique où rediriger selon que la requête vise l'admin ou le front.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('z9qz-e3m*')) {
                return route('admin.login');
            }

            return route('checkout.index');
        });

    })
    ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        if ($request->is('z9qz-e3m/*')) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('home');
    });
})
    ->create();
