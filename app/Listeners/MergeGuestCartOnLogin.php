<?php

namespace App\Listeners;

use App\Actions\Cart\MergeGuestCartAction;
use Illuminate\Auth\Events\Login;

/**
 * Listener sur l'event Login (déclenché automatiquement par Laravel à chaque
 * authentification réussie). Transfère le panier invité (lié au session_id)
 * dans le panier de l'utilisateur connecté.
 *
 * À enregistrer dans App\Providers\AppServiceProvider::boot() ou via
 * Laravel's auto-discovery (si l'app utilise les event discovery).
 */
class MergeGuestCartOnLogin
{
    public function __construct(
        private readonly MergeGuestCartAction $mergeCart,
    ) {}

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (!$user instanceof \App\Models\User) {
            return;
        }

        // On récupère la request courante depuis le container : les events
        // d'auth ne reçoivent pas la Request, mais elle est liée au cycle HTTP.
        $request = request();
        if (!$request) {
            return;
        }

        $this->mergeCart->handle($user, $request);

        // Mise à jour de last_login_at par la même occasion
        $user->forceFill(['last_login_at' => now()])->save();
    }
}
