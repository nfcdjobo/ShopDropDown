<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Carnet d'adresses : l'utilisateur peut enregistrer plusieurs adresses
        // (domicile, bureau...) et en choisir une à la commande.
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('label')->nullable(); // ex. "Maison", "Bureau"
            $table->string('full_name');
            $table->string('phone', 30);

            // Adapté au contexte CI. Ajustable selon les pays de livraison.
            $table->string('country', 2)->default('CI');
            $table->string('city');
            $table->string('commune')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('delivery_notes')->nullable();

            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
