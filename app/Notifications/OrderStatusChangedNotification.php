<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Informe le client d'un changement de statut de sa commande.
 * À déclencher côté service quand l'admin fait avancer l'état :
 *   $order->markAsShipped($admin);
 *   $order->user->notify(new OrderStatusChangedNotification($order));
 *
 * Ou mieux : via un Listener sur un custom event OrderStatusChanged.
 */
class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly ?OrderStatus $fromStatus = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        if (class_exists(WebPushChannel::class)
            && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status  = $this->order->status;
        $url     = route('customer.orders.show', $this->order->order_number);

        $message = (new MailMessage)
            ->subject("Commande {$this->order->order_number} — {$status->label()}")
            ->greeting("Bonjour {$notifiable->first_name},");

        match ($status) {
            OrderStatus::Confirmed => $message->line("Votre commande **{$this->order->order_number}** a été confirmée. Nous préparons votre colis."),
            OrderStatus::Processing => $message->line("Votre commande **{$this->order->order_number}** est en cours de préparation."),
            OrderStatus::Shipped   => $message->line("📦 Votre commande **{$this->order->order_number}** a été expédiée ! Vous la recevrez très prochainement."),
            OrderStatus::Delivered => $message->line("✅ Votre commande **{$this->order->order_number}** a été livrée. Merci pour votre confiance !"),
            OrderStatus::Cancelled => $message->line("Votre commande **{$this->order->order_number}** a été annulée.")
                ->when($this->order->cancellation_reason, fn ($m) => $m->line("Motif : {$this->order->cancellation_reason}")),
            default                => $message->line("Le statut de votre commande **{$this->order->order_number}** est maintenant : {$status->label()}."),
        };

        return $message
            ->action('Voir ma commande', $url)
            ->line('À très bientôt.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'from_status'  => $this->fromStatus?->value,
            'to_status'    => $this->order->status->value,
            'label'        => $this->order->status->label(),
            'message'      => "Commande {$this->order->order_number} : {$this->order->status->label()}",
            'url'          => route('customer.orders.show', $this->order->order_number),
        ];
    }

    public function toWebPush(object $notifiable, $notification)
    {
        $class = WebPushMessage::class;
        if (!class_exists($class)) {
            return null;
        }

        return (new $class())
            ->title("Commande {$this->order->order_number}")
            ->icon('/images/notification-icon.png')
            ->body("Statut : {$this->order->status->label()}")
            ->data(['url' => route('customer.orders.show', $this->order->order_number)]);
    }
}
