<?php

namespace App\Providers;

use App\Models\Banniere;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class BanniereServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::share('banniere', $this->getBanniere());
    }

    /**
     * Récupère la bannière unique avec mise en cache des attributs uniquement.
     */
    protected function getBanniere(): ?Banniere
    {
        try {
            if (! Schema::hasTable('bannieres')) {
                return null;
            }

            // On met en cache UNIQUEMENT les attributs (tableau), pas l'objet Eloquent complet.
            $attributes = Cache::rememberForever('banniere.attributes', function () {
                $banniere = Banniere::query()->first();
                return $banniere ? $banniere->getAttributes() : null;
            });

            if ($attributes === null) {
                return null;
            }

            // On reconstruit un objet Banniere frais à partir des attributs cachés.
            return (new Banniere)->newFromBuilder($attributes);
        } catch (\Throwable $e) {
            Log::error('❌ Erreur getBanniere: ' . $e->getMessage());
            return null;
        }
    }
}
