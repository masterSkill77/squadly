<?php

namespace App\Enums;

enum CompetitionFormat: string
{
    case League = 'league';
    case Cup = 'cup';
    case GroupKnockout = 'group_knockout';
    case LeaguePlayoffs = 'league_playoffs';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::League => 'Championnat',
            self::Cup => 'Coupe',
            self::GroupKnockout => 'Poules + Élimination',
            self::LeaguePlayoffs => 'Championnat + Playoffs',
            self::Custom => 'Personnalisé',
        };
    }
}
