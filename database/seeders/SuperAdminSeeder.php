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
 *  SUPER_ADMIN_EMAIL    = superadmin@shopdropdown.ci
 *  SUPER_ADMIN_PASSWORD = (votre mot de passe sécurisé)
 *  SUPER_ADMIN_FIRST    = Admin
 *  SUPER_ADMIN_LAST     = Principal
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email     = env('SUPER_ADMIN_EMAIL',    'superadmin@shopdropdown.ci');
        $password  = env('SUPER_ADMIN_PASSWORD', 'Admin@ShopDropdown2026!');
        $firstName = env('SUPER_ADMIN_FIRST',    'Admin');
        $lastName  = env('SUPER_ADMIN_LAST',     'Principal');

        // updateOrCreate : idempotent, safe à relancer plusieurs fois
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'first_name'           => $firstName,
                'last_name'            => $lastName,
                'phone'                => null,
                'email'                => $email,
                'password'             => Hash::make($password),
                'role'                 => 'super_admin',
                'is_active'            => true,
                'must_change_password' => false,
                'profile_completed'    => true,
            ]
        );

        $this->command->info('');
        $this->command->info('  ✅ Super admin créé / mis à jour :');
        $this->command->info("     Email    : {$email}");
        $this->command->info("     Nom      : {$user->full_name}");
        $this->command->warn("     ⚠️  Changez le mot de passe en production via .env !");
        $this->command->info('');
    }
}
