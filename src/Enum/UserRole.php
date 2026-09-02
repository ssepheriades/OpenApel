<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRole: string
{
    case Admin = 'ROLE_ADMIN';
    case Member = 'ROLE_MEMBER';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Member => "Membre de l'équipe",
        };
    }
}
