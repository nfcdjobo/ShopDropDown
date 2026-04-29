<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuestCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return !$this->user(); // réservé aux invités
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name'  => ['required', 'string', 'min:2', 'max:60'],

            // Format téléphone Côte d'Ivoire : 8 à 15 chiffres, + et espaces tolérés
            'phone'      => ['required', 'string', 'regex:/^[\d\s\-\+\(\)]{8,20}$/'],

            // L'email DOIT être nouveau — sinon le visiteur est invité à se connecter
            'email'      => [
                'required',
                'string',
                'email:rfc,dns',
                'max:150',
                Rule::unique('users', 'email'),
            ],

            // Infos de livraison
            'city'       => ['required', 'string', 'max:80'],
            'address'    => ['required', 'string', 'max:255'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Cet email est déjà associé à un compte. Veuillez vous connecter ou utiliser un autre email.',
            'phone.regex'  => 'Numéro de téléphone invalide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'prénom',
            'last_name'  => 'nom',
            'phone'      => 'téléphone',
            'email'      => 'adresse email',
            'city'       => 'ville',
            'address'    => 'adresse de livraison',
            'notes'      => 'instructions de livraison',
        ];
    }

    public function shippingData(): array
    {
        $validated = $this->validated();

        return [
            ...$validated,
            'full_name' => trim("{$validated['first_name']} {$validated['last_name']}"),
            'country'   => 'CI',
        ];
    }
}
