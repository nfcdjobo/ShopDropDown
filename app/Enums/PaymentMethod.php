<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CashOnDelivery = 'cash_on_delivery';
    case MobileMoney    = 'mobile_money';
    case Card           = 'card';
    case BankTransfer   = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::CashOnDelivery => 'Paiement à la livraison',
            self::MobileMoney    => 'Mobile Money',
            self::Card           => 'Carte bancaire',
            self::BankTransfer   => 'Virement bancaire',
        };
    }
}
