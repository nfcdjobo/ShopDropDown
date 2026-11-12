<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * E-mail d'invitation / de réinitialisation d'accès d'un administrateur.
 *
 * Étend ResetPassword pour réutiliser resetUrl() : le lien suit donc la configuration
 * de ResetPassword::createUrlUsing() (AppServiceProvider), sans connaître le nom de la route.
 * Aucun mot de passe n'est transmis : l'administrateur choisit le sien via le lien.
 *
 * Volontairement NON mis en file d'attente : un échec d'envoi (SMTP…) doit être détecté
 * immédiatement pour prévenir le super administrateur.
 */
class AdministratorInvitationNotification extends ResetPassword
{
    public function __construct(string $token, private readonly bool $isReset = false)
    {
        parent::__construct($token);
    }

    public function toMail($notifiable): MailMessage
    {
        $app     = config('app.name');
        $url     = $this->resetUrl($notifiable);
        $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);
        $hours   = $minutes >= 120 ? round($minutes / 60) . ' heures' : $minutes . ' minutes';

        $mail = (new MailMessage)
            ->subject($this->isReset
                ? "Réinitialisation de votre accès — {$app}"
                : "Votre accès administrateur — {$app}")
            ->greeting('Bonjour' . ($notifiable->first_name ? ' ' . $notifiable->first_name : '') . ',');

        $mail->line($this->isReset
            ? "Un super administrateur a lancé la réinitialisation de votre accès au back-office de {$app}."
            : "Un compte administrateur vient d'être créé pour vous sur {$app}.");

        return $mail
            ->line('Votre identifiant de connexion : ' . $notifiable->email)
            ->action('Définir mon mot de passe', $url)
            ->line("Ce lien est valable {$hours}. Passé ce délai, demandez à un super administrateur de vous en renvoyer un.")
            ->line('Ensuite, connectez-vous ici : ' . route('admin.login'))
            ->line("Si vous n'êtes pas concerné par ce message, ignorez-le.")
            ->salutation("L'équipe {$app}");
    }
}
