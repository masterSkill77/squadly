<?php

namespace App\Enums;

enum OrganizerRole: string
{
    case Admin = 'admin';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Staff => 'Staff',
        };
    }
}
