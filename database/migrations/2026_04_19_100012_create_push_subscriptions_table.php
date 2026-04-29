<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Abonnements Web Push (protocole standard navigateur, sans service tiers
        // obligatoire). Compatible avec laravel-notification-channels/webpush.
        //
        // Polymorphique (subscribable_id + subscribable_type) pour pouvoir
        // aussi notifier les clients plus tard (ex. "votre commande est expédiée"
        // en push sur leur navigateur). Pour l'instant on s'en sert surtout côté admin.
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('subscribable');

            $table->string('endpoint', 500)->unique();
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('content_encoding')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
