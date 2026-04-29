<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Banniere extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bannieres';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'appelation',
        'logo',
        'slogan',
        'telephone_principal',
        'telephone_secondaire',
        'frais_livraison_defaut',
        'appliquer_frais_livraison_defaut',
        'email',
        'numero_whatsapp',
        'lien_whatsapp',
        'meta_id',
    ];

    protected $casts = [
        'frais_livraison_defaut' => 'integer',
        'appliquer_frais_livraison_defaut' => 'boolean',
    ];

    /**
     * Boot du modèle.
     */
    protected static function booted(): void
    {
        static::creating(function (Banniere $banniere) {
            if (static::query()->exists()) {
                throw new \RuntimeException(
                    "Impossible de créer une nouvelle bannière : la table 'bannieres' ne peut contenir qu'une seule ligne."
                );
            }
        });

        static::deleting(function (Banniere $banniere) {
            throw new \RuntimeException(
                "Impossible de supprimer la bannière : cette ligne est protégée et ne peut pas être supprimée."
            );
        });

        // Vider le cache après toute mise à jour.
        static::saved(fn () => Cache::forget('banniere.instance'));
    }

    /**
     * Récupère l'unique instance de la bannière.
     */
    public static function instance(): self
    {
        return static::query()->first();
    }
}
