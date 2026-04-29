<?php

namespace App\Actions\Checkout;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Crée le compte client généré automatiquement lors d'un checkout invité.
 *
 * Responsabilités :
 *  - Générer un mot de passe temporaire (alphanumérique, lisible)
 *  - Persister le User avec les flags must_change_password et profile_completed
 *  - Retourner le User ET le mot de passe en clair (pour l'inclure dans l'email)
 *
 * Le hashing du mot de passe est délégué au cast 'hashed' du modèle User :
 * on assigne la valeur en clair, Eloquent la hashe automatiquement.
 *
 * Cette action est volontairement minimaliste : elle ne fait qu'une seule
 * chose et peut être testée de façon isolée (pas d'I/O panier/commande).
 */
class CreateGuestUserAction
{
    /**
     * @param  array $guestData  Résultat de GuestCheckoutRequest::shippingData() :
     *                           first_name, last_name, phone, email, ...
     * @return array{0: User, 1: string}  [$user, $plainPassword]
     */
    public function handle(array $guestData): array
    {
        $plainPassword = User::generateDefaultPassword();

        $user = User::create([
            'first_name'           => $guestData['first_name'],
            'last_name'            => $guestData['last_name'],
            'phone'                => $guestData['phone'],
            'email'                => $guestData['email'],
            'password'             => $plainPassword, // cast 'hashed' → bcrypt automatique
            'role'                 => UserRole::Customer,
            'must_change_password' => true,  // forcera le changement au 1er login
            'profile_completed'    => false, // banner "complétez votre profil" dans le dashboard
            'is_active'            => true,
        ]);

        return [$user, $plainPassword];
    }
}
