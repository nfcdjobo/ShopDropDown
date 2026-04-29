<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_proofs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // Chemin du fichier sur le disque 'public'
            $table->string('path');

            // Légende affichée sous la preuve (témoignage court, contexte, résultat)
            $table->string('caption')->nullable();

            // Texte alternatif pour l'accessibilité / SEO
            $table->string('alt_text')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_proofs');
    }
};
