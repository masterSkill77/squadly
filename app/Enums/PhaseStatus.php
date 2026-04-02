<?php

namespace App\Enums;

enum PhaseStatus: string
{
    case Pending = 'pending';
    case Ongoing = 'ongoing';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Ongoing => 'En cours',
            self::Finished => 'Terminée',
        };
    }
}
