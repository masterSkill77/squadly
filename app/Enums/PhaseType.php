<?php

namespace App\Enums;

enum PhaseType: string
{
    case Group = 'group';
    case Knockout = 'knockout';
    case RoundRobin = 'round_robin';
    case Single = 'single';

    public function label(): string
    {
        return match ($this) {
            self::Group => 'Poule',
            self::Knockout => 'Élimination directe',
            self::RoundRobin => 'Aller-retour',
            self::Single => 'Match unique',
        };
    }
}
