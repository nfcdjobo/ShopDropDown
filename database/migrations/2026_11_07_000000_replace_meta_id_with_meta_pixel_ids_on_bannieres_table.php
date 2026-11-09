<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remplace le pixel unique (meta_id) par une liste de pixels (meta_pixel_ids, JSON).
     * L'ancien identifiant est repris comme premier élément de la liste.
     */
    public function up(): void
    {
        Schema::table('bannieres', function (Blueprint $table) {
            $table->json('meta_pixel_ids')->nullable()->after('meta_id')
                ->comment('Liste des identifiants de Pixels Meta (Facebook)');
        });

        DB::table('bannieres')->get()->each(function ($row) {
            $ancien = trim((string) $row->meta_id);

            DB::table('bannieres')
                ->where('id', $row->id)
                ->update(['meta_pixel_ids' => json_encode($ancien !== '' ? [$ancien] : [])]);
        });

        Schema::table('bannieres', function (Blueprint $table) {
            $table->dropColumn('meta_id');
        });
    }

    public function down(): void
    {
        Schema::table('bannieres', function (Blueprint $table) {
            $table->string('meta_id')->nullable()->after('lien_whatsapp')
                ->comment('Identifiant du Pixel Meta (Facebook)');
        });

        // On restitue le premier pixel de la liste dans l'ancienne colonne
        DB::table('bannieres')->get()->each(function ($row) {
            $liste = json_decode((string) $row->meta_pixel_ids, true) ?: [];

            DB::table('bannieres')
                ->where('id', $row->id)
                ->update(['meta_id' => $liste[0] ?? null]);
        });

        Schema::table('bannieres', function (Blueprint $table) {
            $table->dropColumn('meta_pixel_ids');
        });
    }
};
