<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;

class OrderObserver
{
    /**
     * Déclenché après la création d'une commande.
     *
     * Note importante : cet observer crée la 1re entrée dans order_status_histories.
     * La décrémentation du stock et l'incrémentation de orders_count sont faites
     * dans le service de checkout (PlaceOrderAction) à l'intérieur de la même
     * transaction que la création des order_items — ce qui garantit que le
     * stock ne bouge que si tout réussit.
     */
    public function created(Order $order): void
    {
        // $rawStatus = $order->getRawOriginal('status')
        //           ?? $order->status?->value
        //           ?? OrderStatus::Pending->value;
        $rawStatus = $order->getRawOriginal('status')   // ① valeur PHP brute (si passée à create())
          ?? $order->status?->value              // ② via le cast (null-safe)
          ?? OrderStatus::Pending->value;        // ③ fallback dur : 'pending'

        $order->statusHistories()->create([
            'from_status' => null,
            'to_status'   => $rawStatus,
            'note'        => 'Commande créée',
            'changed_by'  => $order->user_id,
        ]);
    }
}
