<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Numéro public type "SD-2026-00001", généré côté application.
            $table->string('order_number')->unique();

            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete(); // on n'efface jamais un user qui a des commandes

            $table->foreignUuid('address_id')->nullable()->constrained('addresses')->nullOnDelete();

            // ---- Snapshot des infos de livraison ----
            // On duplique volontairement les infos depuis addresses/users : si
            // l'utilisateur modifie son adresse ou son nom plus tard, la commande
            // garde l'état exact du moment de la validation. Indispensable pour
            // la traçabilité et les litiges.
            $table->string('shipping_full_name');
            $table->string('shipping_phone', 30);
            $table->string('shipping_email');
            $table->string('shipping_country', 2)->default('CI');
            $table->string('shipping_city');
            $table->string('shipping_address');
            $table->text('shipping_notes')->nullable();

            // ---- Montants ----
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('XOF');

            // ---- Workflow de commande ----
            // pending    : reçue, attend traitement admin
            // confirmed  : admin a confirmé (appel client ok, stock ok)
            // processing : en préparation/emballage
            // shipped    : expédiée au livreur
            // delivered  : livrée au client
            // cancelled  : annulée
            // refunded   : remboursée
            $table->enum('status', [
                'pending',
                'confirm',
                'process',
                'ship',
                'deliver',
                'cancel',
                'refunded',
            ])->default('pending');

            // ---- Paiement : structure prête, pas encore utilisée ----
            // Aujourd'hui toutes les commandes sont en 'cash_on_delivery' / 'unpaid'
            // jusqu'à la livraison. Quand le paiement en ligne sera ajouté (mobile
            // money, carte), aucune migration de schéma ne sera nécessaire.
            $table->enum('payment_status', [
                'unpaid',
                'pending',
                'paid',
                'failed',
                'refunded',
            ])->default('unpaid');

            $table->enum('payment_method', [
                'cash_on_delivery',
                'mobile_money',
                'card',
                'bank_transfer',
            ])->default('cash_on_delivery');

            $table->string('payment_reference')->nullable(); // ID transaction externe
            $table->timestamp('paid_at')->nullable();

            // ---- Traçabilité admin ----
            $table->text('admin_notes')->nullable();

            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Horodatage des étapes clés (pour KPI et timeline client)
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            // Contexte technique (anti-fraude, support)
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index pour les requêtes fréquentes du back-office
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['payment_status', 'status']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
