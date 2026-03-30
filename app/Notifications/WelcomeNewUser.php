<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNewUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenue sur Squadly !')
            ->greeting("Bienvenue {$notifiable->name} !")
            ->line("Nous sommes ravis de vous compter parmi nous.")
            ->line("Squadly vous permet de gérer votre club sportif en toute simplicité : organisation des équipes, planification des événements, convocations et bien plus encore.")
            ->line("Pour commencer, créez votre club et invitez vos membres.")
            ->action('Accéder à Squadly', url('/dashboard'))
            ->line("Si vous avez la moindre question, n'hésitez pas à nous contacter.")
            ->salutation("Sportivement,\nL'équipe **Squadly**");
    }
}
