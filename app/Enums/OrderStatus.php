<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirm';
    case Processing = 'process';
    case Shipped    = 'ship';
    case Delivered  = 'deliver';
    case Cancelled  = 'cancel';
    case Refunded   = 'refunded';








    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'En attente',
            self::Confirmed  => 'Confirmée',
            self::Processing => 'En préparation',
            self::Shipped    => 'Expédiée',
            self::Delivered  => 'Livrée',
            self::Cancelled  => 'Annulée',
            self::Refunded   => 'Remboursée',
        };
    }

    /**
     * Couleur indicative pour les badges UI (Tailwind palette).
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending    => 'yellow',
            self::Confirmed  => 'blue',
            self::Processing => 'indigo',
            self::Shipped    => 'purple',
            self::Delivered  => 'green',
            self::Cancelled  => 'red',
            self::Refunded   => 'gray',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Refunded], true);
    }

    public function isActive(): bool
    {
        return !$this->isFinal();
    }

    /**
     * Machine à états : transitions autorisées depuis le statut courant.
     *
     * @return array<OrderStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending    => [self::Confirmed, self::Cancelled],
            self::Confirmed  => [self::Processing, self::Cancelled],
            self::Processing => [self::Shipped, self::Cancelled],
            self::Shipped    => [self::Delivered, self::Cancelled],
            self::Delivered  => [self::Refunded],
            self::Cancelled, self::Refunded => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }
}
