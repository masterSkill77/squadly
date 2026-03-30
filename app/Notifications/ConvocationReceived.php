<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConvocationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Convocation : {$this->event->title}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Vous avez été convoqué(e) pour l'événement suivant :")
            ->line("**{$this->event->title}** ({$this->event->type_label})")
            ->line("Equipe : {$this->event->team->name}")
            ->line("Date : {$this->event->start_at->format('d/m/Y à H:i')}")
            ->line("Lieu : {$this->event->location}")
            ->action('Répondre à la convocation', url('/mes-convocations'))
            ->line('Merci de confirmer votre disponibilité.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_type' => $this->event->type_label,
            'event_date' => $this->event->start_at->toIso8601String(),
            'team_name' => $this->event->team->name,
            'message' => "Vous êtes convoqué(e) pour « {$this->event->title} » le {$this->event->start_at->format('d/m/Y à H:i')}.",
        ];
    }
}
