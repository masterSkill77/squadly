<?php

namespace App\Enums;

enum GameStatus: string
{
    case Scheduled = 'scheduled';
    case Ongoing = 'ongoing';
    case Finished = 'finished';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Programmé',
            self::Ongoing => 'En cours',
            self::Finished => 'Terminé',
            self::Cancelled => 'Annulé',
        };
    }
}
