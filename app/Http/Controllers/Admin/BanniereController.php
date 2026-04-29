<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banniere;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BanniereController extends Controller
{
    /**
     * Affiche le formulaire de mise à jour de la bannière.
     */
    public function edit(): View
    {
        $banniere = Banniere::instance();

        return view('admin.bannieres.edit', compact('banniere'));
    }

    /**
     * Met à jour les informations de la bannière unique.
     */
    public function update(Request $request): RedirectResponse
    {
        $banniere = Banniere::instance();

        $validated = $request->validate([
            'appelation' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'telephone_principal' => ['required', 'string', 'max:30'],
            'telephone_secondaire' => ['nullable', 'string', 'max:30'],
            'frais_livraison_defaut' => ['required', 'integer', 'min:0'],
            'appliquer_frais_livraison_defaut' => ['required', 'boolean'],
            'email' => ['required', 'email', 'max:100'],
            'numero_whatsapp' => ['nullable', 'string', 'max:30'],
            'lien_whatsapp' => ['nullable', 'url', 'max:255'],
            'meta_id' => ['nullable', 'string', 'regex:/^\d{15,16}$/'],
        ]);

        if ($request->hasFile('logo')) {
            if ($banniere->logo && Storage::disk('public')->exists($banniere->logo)) {
                Storage::disk('public')->delete($banniere->logo);
            }

            $validated['logo'] = $request->file('logo')->store('bannieres', 'public');
        }

        $banniere->update($validated);

        return redirect()
            ->route('admin.banniere.edit')
            ->with('success', 'La bannière a été mise à jour avec succès.');
    }

    /**
     * Supprime uniquement le logo de la bannière.
     */
    public function supprimerLogo(): RedirectResponse
    {
        $banniere = Banniere::instance();

        if ($banniere->logo && Storage::disk('public')->exists($banniere->logo)) {
            Storage::disk('public')->delete($banniere->logo);
        }

        $banniere->update(['logo' => null]);

        return redirect()
            ->route('admin.banniere.edit')
            ->with('success', 'Le logo a été supprimé.');
    }
}
