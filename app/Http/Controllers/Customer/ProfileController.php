<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des infos personnelles du client. Après la 1re commande, le dashboard
 * invite à passer ici pour compléter le profil (adresses, téléphone secondaire, etc.).
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('addresses');

        return view('customer.profile.edit', [
            'user'      => $user,
            'addresses' => $user->addresses,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            ...$request->validated(),
            'profile_completed' => true, // coche le flag définitivement
        ]);

        return redirect()
            ->route('customer.profile.edit')
            ->with('success', 'Profil mis à jour.');
    }
}
