<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

use App\Listeners\MergeGuestCartOnLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    // public function boot(): void
    // {
    //     // Derrière Traefik (HTTPS terminé en amont) : liens, formulaires
    //     // et manifest générés en https.
    //     if ($this->app->isProduction()) {
    //         URL::forceScheme('https');
    //     }

    //     // Observers
    //     Order::observe(OrderObserver::class);

    //     // En développement : alerte si N+1 queries ou attribut inconnu.
    //     if ($this->app->isLocal()) {
    //         Model::shouldBeStrict();
    //     }

    //     // Lève une exception sur le lazy loading (N+1) hors production.
    //     Model::preventLazyLoading(! $this->app->isProduction());

    //     // Fusion automatique du panier invité à chaque login réussi.
    //     Event::listen(Login::class, MergeGuestCartOnLogin::class);
    // }

    public function boot(): void
{
    $isProduction = $this->app->environment('production');

    // Derrière Traefik (HTTPS terminé en amont)
    if ($isProduction) {
        URL::forceScheme('https');
    }

    // Observers
    Order::observe(OrderObserver::class);

    // En développement : alerte si N+1 queries ou attribut inconnu.
    if ($this->app->environment('local')) {
        Model::shouldBeStrict();
    }

    // Lève une exception sur le lazy loading (N+1) hors production.
    Model::preventLazyLoading(! $isProduction);

    // Fusion automatique du panier invité à chaque login réussi.
    Event::listen(Login::class, MergeGuestCartOnLogin::class);
}
}
