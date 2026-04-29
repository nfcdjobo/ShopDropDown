<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Durée d'urgence glissante affichée à chaque visiteur.
            // Le compteur redémarre côté client à chaque expiration —
            // l'offre ne se termine jamais réellement.
            $table->unsignedSmallInteger('offer_duration_hours')
                ->nullable()
                ->after('availability_label');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('offer_duration_hours');
        });
    }
};
