<?php

namespace App\Notifications;

use App\Models\Club;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberInvited extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Club $club,
        public string $temporaryPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Bienvenue dans le club {$this->club->name} sur Squadly")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Vous avez été ajouté(e) au club **{$this->club->name}** sur Squadly.")
            ->line('Voici vos identifiants de connexion :')
            ->line("**Email :** {$notifiable->email}")
            ->line("**Mot de passe temporaire :** {$this->temporaryPassword}")
            ->action('Se connecter', url('/login'))
            ->line('Nous vous conseillons de modifier votre mot de passe après votre première connexion.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'club_id' => $this->club->id,
            'club_name' => $this->club->name,
            'message' => "Bienvenue dans le club « {$this->club->name} » !",
        ];
    }
}
