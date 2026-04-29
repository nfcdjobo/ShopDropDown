<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;

class MergeGuestCartAction
{
    /**
     * À appeler juste après login/inscription : récupère le panier invité
     * lié au session_id courant et le fusionne dans celui de l'utilisateur.
     *
     * Branché sur l'event Login via App\Listeners\MergeGuestCartOnLogin.
     */
    public function handle(User $user, Request $request): void
    {
        if (!$request->hasSession()) {
            return;
        }

        $sessionId = $request->session()->getId();

        $guestCart = Cart::forGuest($sessionId)->with('items')->first();
        if (!$guestCart || $guestCart->items->isEmpty()) {
            $guestCart?->delete();
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
        $userCart->mergeFrom($guestCart);
    }
}
