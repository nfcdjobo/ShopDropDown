<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation de commande envoyée au client déjà inscrit (le nouveau client
 * reçoit WelcomeWithDefaultPasswordNotification à la place).
 */
class OrderConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('customer.orders.show', $this->order->order_number);

        return (new MailMessage)
            ->subject("Commande confirmée — {$this->order->order_number}")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line('Votre commande a bien été prise en compte et est en cours de traitement.')
            ->line("**Numéro :** {$this->order->order_number}")
            ->line("**Total :** {$this->order->formatted_total}")
            ->action('Suivre ma commande', $url)
            ->line('Merci de votre confiance.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'total'        => (float) $this->order->total,
            'status'       => $this->order->status->value,
            'message'      => "Commande {$this->order->order_number} confirmée",
            'url'          => route('customer.orders.show', $this->order->order_number),
        ];
    }
}
