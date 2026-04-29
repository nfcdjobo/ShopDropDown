<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historique complet des changements de statut. Alimenté automatiquement
        // par un Observer sur Order. Permet d'afficher une timeline côté client
        // ("Commande reçue" → "Confirmée" → "Expédiée" → "Livrée") et de tracer
        // qui a fait quoi côté admin.
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // $table->foreignId('order_id')
            //     ->constrained()
            //     ->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();

            $table->string('from_status')->nullable(); // null à la création
            $table->string('to_status');
            $table->text('note')->nullable();

            // Qui a effectué le changement (admin, système via webhook paiement...)
            // $table->foreignId('changed_by')
            //     ->nullable()
            //     ->constrained('users')
            //     ->nullOnDelete();
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
