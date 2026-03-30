<?php

namespace App\Enums;

enum AnnouncementTarget: string
{
    case Club = 'club';
    case Section = 'section';
    case Teams = 'teams';

    public function label(): string
    {
        return match ($this) {
            self::Club => 'Tout le club',
            self::Section => 'Section',
            self::Teams => 'Équipe(s)',
        };
    }
}
