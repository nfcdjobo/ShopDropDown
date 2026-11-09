<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crée le compte super administrateur par défaut.
 *
 * Exécution :
 *   php artisan db:seed --class=SuperAdminSeeder
 *
 * Ou via DatabaseSeeder :
 *   php artisan db:seed
 *
 * Les informations de connexion sont lues depuis .env pour ne jamais
 * stocker de credentials en dur dans le code source.
 *
 *  SUPER_ADMIN_EMAIL    = superadmin@ShopDrop.ci
 *  SUPER_ADMIN_PASSWORD = (votre mot de passe sécurisé)
 *  SUPER_ADMIN_FIRST    = Admin
 *  SUPER_ADMIN_LAST     = Principal
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email     = env('SUPER_ADMIN_EMAIL',    'superadmin@ShopDrop.ci');
        $password  = env('SUPER_ADMIN_PASSWORD', 'Admin@ShopDrop2026!');
        $firstName = env('SUPER_ADMIN_FIRST',    'Admin');
        $lastName  = env('SUPER_ADMIN_LAST',     'Principal');

        // firstOrCreate : si l'email existe déjà, rien n'est modifié
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'first_name'           => $firstName,
                'last_name'            => $lastName,
                'phone'                => null,
                'password'             => Hash::make($password),
                'role'                 => 'super_admin',
                'is_active'            => true,
                'must_change_password' => false,
                'profile_completed'    => true,
            ]
        );

        $this->command->info('');
        if ($user->wasRecentlyCreated) {
            $this->command->info("  ✅ Super admin créé : {$email}");
            $this->command->warn('  ⚠️  Changez le mot de passe en production via .env !');
        } else {
            $this->command->info("  ℹ️  Super admin déjà existant, aucune modification : {$email}");
        }
        $this->command->info('');
    }
}
