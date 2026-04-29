<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;

/**
 * Notification envoyée aux administrateurs à chaque nouvelle commande.
 *
 * Canaux :
 *  - mail     : email de notification
 *  - database : pour le badge de notifications dans le back-office
 *  - webpush  : notification push navigateur en temps réel (si le package
 *               laravel-notification-channels/webpush est installé)
 *
 * Implémente ShouldQueue → part en file d'attente et ne bloque pas la réponse HTTP.
 */
class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order)
    {
    }

    /**
     * Canaux de livraison. webpush n'est ajouté que si le package est installé
     * ET que l'admin a au moins un abonnement push.
     */
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
        $url = route('customer.orders.show', $this->order->order_number);

        return (new MailMessage)
            ->subject("🛒 Nouvelle commande {$this->order->order_number}")
            ->greeting('Nouvelle commande !')
            ->line("Commande **{$this->order->order_number}** de **{$this->order->shipping_full_name}**.")
            ->line("Téléphone : {$this->order->shipping_phone}")
            ->line("Montant  : {$this->order->formatted_total}")
            ->line("Articles : {$this->order->items_count}")
            ->action('Voir la commande', $url)
            ->line('Merci de la traiter rapidement.');
    }

    /**
     * Payload stocké en base (canal 'database'), lu par le badge du back-office.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id'      => $this->order->id,
            'order_number'  => $this->order->order_number,
            'customer_name' => $this->order->shipping_full_name,
            'customer_phone' => $this->order->shipping_phone,
            'total'         => (float) $this->order->total,
            'currency'      => $this->order->currency,
            'items_count'   => $this->order->items_count,
            'url'           => route('customer.orders.show', $this->order->order_number),
            'message'       => "Nouvelle commande #{$this->order->order_number} de {$this->order->shipping_full_name}",
        ];
    }

    /**
     * Payload Web Push. Nécessite :
     *   composer require laravel-notification-channels/webpush
     *
     * La méthode n'est appelée que si le canal est inclus dans via().
     */
    public function toWebPush(object $notifiable, $notification)
    {
        $class = \NotificationChannels\WebPush\WebPushMessage::class;
        if (!class_exists($class)) {
            return null;
        }

        return (new $class())
            ->title("🛒 Nouvelle commande #{$this->order->order_number}")
            ->icon('/images/notification-icon.png')
            ->body("{$this->order->shipping_full_name} — {$this->order->formatted_total}")
            ->action('Voir', 'view_order')
            ->data(['url' => route('customer.orders.show', $this->order->order_number)])
            ->vibrate([200, 100, 200])
            ->options(['TTL' => 3600]);
    }
}
