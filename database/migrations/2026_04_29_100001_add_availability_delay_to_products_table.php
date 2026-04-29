<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Délai de disponibilité / livraison annoncé au client.
            //
            // Stratégie : on stocke un intervalle structuré (min/max jours)
            // pour permettre tri, filtrage et affichage normalisé,
            // ET un libellé personnalisé qui surcharge l'intervalle
            // pour les cas marketing : « En précommande », « Livraison 24h », etc.
            //
            // Pertinent pour 'simple' et 'package'. Les 'item' héritent du package parent.
            $table->unsignedSmallInteger('availability_min_days')
                ->nullable()
                ->after('low_stock_threshold');

            $table->unsignedSmallInteger('availability_max_days')
                ->nullable()
                ->after('availability_min_days');

            $table->string('availability_label')
                ->nullable()
                ->after('availability_max_days');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'availability_min_days',
                'availability_max_days',
                'availability_label',
            ]);
        });
    }
};
