<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identité
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone', 30)->nullable()->index();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Rôles : customer = visiteur inscrit, admin = gestionnaire commandes,
            // super_admin = gestion complète (produits, users, etc.)
            $table->enum('role', ['customer', 'admin', 'super_admin'])
                ->default('customer')
                ->index();

            // Flags métier
            // - must_change_password : true quand le compte est créé automatiquement
            //   après commande avec un mot de passe par défaut envoyé par email.
            //   L'utilisateur sera forcé de le changer au premier login.
            $table->boolean('must_change_password')->default(false);

            // - profile_completed : false tant que l'utilisateur n'a pas complété
            //   ses infos après sa 1re commande. Permet d'afficher le prompt
            //   "complétez votre profil" sur son espace.
            $table->boolean('profile_completed')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
