<?php

namespace App\Listeners;

use App\Notifications\WelcomeNewUser;
use Illuminate\Auth\Events\Registered;

class SendWelcomeNotification
{
    public function handle(Registered $event): void
    {
        $event->user->notify(new WelcomeNewUser);
    }
}
