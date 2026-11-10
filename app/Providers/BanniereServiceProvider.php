<?php

namespace App\Providers;

use App\Models\Banniere;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class BanniereServiceProvider extends ServiceProvider
{
    private ?Banniere $banniere = null;
    private bool $loaded = false;

    public function boot(): void
    {
        // Évalué à l'affichage d'une vue, pas au démarrage de l'application
        View::composer('*', function ($view) {
            $view->with('banniere', $this->getBanniere());
        });
    }

    protected function getBanniere(): ?Banniere
    {
        if ($this->loaded) {
            return $this->banniere;   // une seule lecture par requête
        }
        $this->loaded = true;

        try {
            $attributes = Cache::rememberForever('banniere.attributes', function () {
                return Banniere::query()->first()?->getAttributes();
            });

            if ($attributes !== null) {
                $this->banniere = (new Banniere)->newFromBuilder($attributes);
            }
        } catch (\Throwable $e) {
            Log::error('Erreur getBanniere: ' . $e->getMessage());
        }

        return $this->banniere;
    }
}
