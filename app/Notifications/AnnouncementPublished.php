<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Announcement $announcement,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouvelle annonce : {$this->announcement->title}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Une nouvelle annonce a été publiée :")
            ->line("**{$this->announcement->title}**")
            ->line($this->announcement->content)
            ->action('Voir les annonces', url('/annonces'))
            ->line('Bonne journée !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'author_name' => $this->announcement->author->name,
            'message' => "Nouvelle annonce : « {$this->announcement->title} »",
        ];
    }
}
