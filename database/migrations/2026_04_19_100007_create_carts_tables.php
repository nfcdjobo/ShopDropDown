<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Panier persistant en base.
        // - Visiteur non connecté : session_id renseigné, user_id null.
        // - Utilisateur connecté  : user_id renseigné.
        // Au login/inscription, le panier invité est fusionné avec celui de l'utilisateur.
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();

            $table->string('session_id')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            // Un seul panier actif par user (simplifie la fusion)
            $table->unique('user_id');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('cart_id')->constrained('carts')->cascadeOnDelete();

            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);
            // Snapshot du prix au moment de l'ajout — protège contre un changement
            // de prix entre l'ajout au panier et la validation de la commande.
            $table->decimal('unit_price', 12, 2);

            // Variations éventuelles (taille, couleur sélectionnée, etc.)
            $table->json('options')->nullable();

            $table->timestamps();

            // Un produit n'apparaît qu'une fois par panier (on incrémente quantity).
            $table->unique(['cart_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
