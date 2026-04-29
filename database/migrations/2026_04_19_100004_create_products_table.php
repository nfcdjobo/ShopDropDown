<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // ─── Type & hiérarchie de package ─────────────────────────────
            // 'simple'  : produit autonome, vendable seul
            // 'package' : offre groupée (porte le prix global + le stock)
            // 'item'    : sous-produit appartenant à un package, non vendable seul
            $table->enum('type', ['simple', 'package', 'item'])->default('simple');


            // Rôle dans le package : 'principal' (mis en avant) ou 'bonus'
            $table->enum('package_role', ['principal', 'bonus'])->nullable();

            // Ordre d'affichage des sous-produits dans le package
            $table->unsignedSmallInteger('package_sort_order')->default(0);

            // ─── Identification ───────────────────────────────────────────
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();

            // ─── Contenu ──────────────────────────────────────────────────
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('video_url')->nullable(); // YouTube / Vimeo

            // ─── Prix ─────────────────────────────────────────────────────
            // null pour les 'item' (le prix est porté par le package parent)
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('compare_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();

            // ─── Stock ────────────────────────────────────────────────────
            // Pertinent pour 'simple' et 'package' uniquement
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('track_stock')->default(true);
            $table->unsignedInteger('low_stock_threshold')->default(5);

            // ─── Visibilité ───────────────────────────────────────────────
            // Les 'item' héritent de la visibilité du package parent
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // ─── Attributs flexibles (taille, couleur, poids…) ────────────
            $table->json('attributes')->nullable();

            // ─── SEO ──────────────────────────────────────────────────────
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // ─── Analytics ────────────────────────────────────────────────
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('orders_count')->default(0);

            // ─── Évaluation affichée ──────────────────────────────────────
            // Saisie par l'admin, indépendante des avis curatés.
            // rating_value : 0.0 à 5.0 par pas de 0.1 → permet « 4.7 », « 4.8 »…
            $table->decimal('rating_value', 2, 1)->nullable();
            $table->unsignedInteger('rating_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // ─── Index ────────────────────────────────────────────────────
            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'created_at']);
            $table->index(['type', 'is_active']);

        });


        Schema::table('products', function (Blueprint $table) {
            // Pour les 'item' uniquement : pointe vers la ligne 'package' parente
            $table->foreignUuid('parent_id')->nullable()->constrained('products')->cascadeOnDelete();

            $table->index(['parent_id', 'package_sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
