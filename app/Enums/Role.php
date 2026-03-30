<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin_club';
    case Coach = 'coach';
    case Membre = 'membre';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Président',
            self::Coach => 'Coach',
            self::Membre => 'Membre',
        };
    }
}
