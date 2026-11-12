<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail contenant les accès provisoires (identifiant + mot de passe) d'un administrateur.
 *
 * Non mis en file d'attente : le mot de passe en clair ne doit jamais être sérialisé
 * dans une table de jobs, et un échec d'envoi doit être détecté immédiatement.
 */
class AdministratorCredentialsNotification extends Notification
{
    public function __construct(
        private readonly string $plainPassword,
        private readonly bool $isReset = false,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app = config('app.name');

        return (new MailMessage)
            ->subject($this->isReset
                ? "Vos nouveaux accès — {$app}"
                : "Vos accès administrateur — {$app}")
            ->greeting('Bonjour' . ($notifiable->first_name ? ' ' . $notifiable->first_name : '') . ',')
            ->line($this->isReset
                ? "Votre mot de passe a été réinitialisé par un super administrateur de {$app}."
                : "Un compte administrateur vient d'être créé pour vous sur {$app}.")
            ->line('**Identifiant :** ' . $notifiable->email)
            ->line('**Mot de passe provisoire :** `' . $this->plainPassword . '`')
            ->action('Se connecter', route('admin.login'))
            ->line('Pour votre sécurité, remplacez ce mot de passe provisoire par un mot de passe personnel dès votre première connexion, puis supprimez ce message.')
            ->salutation("L'équipe {$app}");
    }
}
