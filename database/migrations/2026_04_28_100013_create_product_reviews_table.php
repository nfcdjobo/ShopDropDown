<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // S'attache à un 'simple' OU à un 'package' (jamais à un 'item')
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();

            // Identité fictive du « client » saisie par l'admin
            $table->string('author_name');
            $table->string('author_location')->nullable(); // ex : « Cocody, Abidjan »
            $table->string('author_avatar')->nullable();   // photo optionnelle

            // Note de 1 à 5
            $table->unsignedTinyInteger('rating');

            // Contenu de l'avis
            $table->string('title')->nullable();
            $table->text('comment');

            // Date d'affichage (pour antidater l'avis et donner un effet « historique »)
            $table->date('reviewed_on')->nullable();

            // Affichage
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['product_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
