<?php

namespace App\Enums;

enum EventType: string
{
    case Training = 'training';
    case Match = 'match';
    case MediaDay = 'media_day';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Training => 'Entraînement',
            self::Match => 'Match',
            self::MediaDay => 'Média Day',
            self::Other => 'Autre',
        };
    }
}
