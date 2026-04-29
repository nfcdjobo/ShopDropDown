<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Envoyée UNE SEULE FOIS au client créé automatiquement lors du checkout invité.
 *
 * Contient :
 *  - la confirmation de la commande
 *  - les identifiants de connexion (email + mot de passe par défaut en clair)
 *  - l'invitation à changer le mot de passe dès la première connexion
 *
 * /!\ Le mot de passe est transmis ici en clair : c'est intentionnel et limité
 * à cet unique email. Il est immédiatement invalidé au premier login (flag
 * must_change_password force l'utilisateur à le changer).
 */
class WelcomeWithDefaultPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly string $plainPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = url('/login');

        return (new MailMessage)
            ->subject("Bienvenue sur Shop Dropdown — Commande {$this->order->order_number}")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line('Merci pour votre commande ! Elle a bien été enregistrée et est en cours de traitement.')
            ->line("**Numéro de commande :** {$this->order->order_number}")
            ->line("**Montant total :** {$this->order->formatted_total}")
            ->line('---')
            ->line('Nous avons créé un compte pour vous permettre de suivre votre commande :')
            ->line("**Email :** {$notifiable->email}")
            ->line("**Mot de passe temporaire :** `{$this->plainPassword}`")
            ->line('⚠️ Pour votre sécurité, il vous sera demandé de changer ce mot de passe dès votre première connexion.')
            ->action('Suivre ma commande', $loginUrl)
            ->line('À très bientôt sur Shop Dropdown.');
    }
}
