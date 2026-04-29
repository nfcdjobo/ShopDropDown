<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer   => 'Client',
            self::Admin      => 'Administrateur',
            self::SuperAdmin => 'Super administrateur',
        };
    }

    /**
     * Rôles considérés comme administratifs (accès back-office).
     *
     * @return array<UserRole>
     */
    public static function adminRoles(): array
    {
        return [self::Admin, self::SuperAdmin];
    }
}
