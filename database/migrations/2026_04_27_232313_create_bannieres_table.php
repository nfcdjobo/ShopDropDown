<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Cette table doit contenir d'une seule et unique ligne. Cette ligne est par defaut ajoutée lors de la migration. NB: impossible de supprimer cette ligne
     */
    public function up(): void
    {
        Schema::create('bannieres', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('appelation');
            $table->string('logo')->nullable();
            $table->string('slogan')->nullable();
            $table->string('telephone_principal');
            $table->string('telephone_secondaire')->nullable();
            $table->integer('frais_livraison_defaut')->default(0);
            $table->boolean('appliquer_frais_livraison_defaut')->default(false);
            $table->string('email', 100);
            $table->string('numero_whatsapp')->nullable();
            $table->string('lien_whatsapp')->nullable();
            $table->string('meta_id')->nullable()->comment('Identifiant du Pixel Meta (Facebook)');

            $table->timestamps();
        });

        // ─── Donnée par défaut (ligne unique) ──────────────────────────
        DB::table('bannieres')->insert([
            'id'                                => (string) Str::uuid(),
            'appelation'                        => 'Shop Drop',
            'logo'                              => null,
            'slogan'                            => 'Produits soigneusement sélectionnés, livrés rapidement en Côte d\'Ivoire.',
            'telephone_principal'               => '+225 01 40 94 03 30',
            'telephone_secondaire'              => null,
            'frais_livraison_defaut'            => 1000,
            'appliquer_frais_livraison_defaut'  => false,
            'email'                             => 'hello@ShopDrop.ci',
            'numero_whatsapp'                   => '+225 01 40 94 03 30',
            'lien_whatsapp'                     => 'https://wa.me/+2250140940330',
            'meta_id'                           => '954379014431559',
            'created_at'                        => now(),
            'updated_at'                        => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bannieres');
    }
};
