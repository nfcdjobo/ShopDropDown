<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use Illuminate\Http\Request;

class GetOrCreateCartAction
{
    /**
     * Retourne le panier actif pour la requête courante.
     *
     *  - Utilisateur connecté  : son propre panier (créé si inexistant).
     *  - Visiteur non connecté : panier lié au session_id du navigateur.
     *
     * Le session_id Laravel est persistant tant que le cookie de session est
     * présent — le panier invité survit donc à une fermeture d'onglet.
     */
    public function handle(Request $request): Cart
    {
        if ($user = $request->user()) {
            return Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['expires_at' => now()->addDays(60)]
            );
        }

        // Garantit qu'une session existe (démarre une si ce n'est pas le cas).
        if (!$request->hasSession() || !$request->session()->isStarted()) {
            $request->session()->start();
        }

        $sessionId = $request->session()->getId();

        return Cart::firstOrCreate(
            ['user_id' => null, 'session_id' => $sessionId],
            ['expires_at' => now()->addDays(30)]
        );
    }
}
