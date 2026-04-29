<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

use App\Listeners\MergeGuestCartOnLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Observers
        Order::observe(OrderObserver::class);

        // En développement : alerte si N+1 queries ou attribut inconnu.
        // À désactiver en production via la valeur de app.debug.
        if ($this->app->isLocal()) {
            Model::shouldBeStrict();
        }

        // Empêche la suppression en masse accidentelle (Order::query()->delete()
        // doit être explicitement autorisé via ->forceDelete()).
        Model::preventLazyLoading(!$this->app->isProduction());

        // Fusion automatique du panier invité à chaque login réussi.
        Event::listen(Login::class, MergeGuestCartOnLogin::class);
    }
}
