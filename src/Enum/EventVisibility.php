<?php

declare(strict_types=1);

namespace App\Enum;

enum EventVisibility: string
{
    case Visible = 'visible';
    case Hidden = 'hidden';
    case GreyedOut = 'greyed_out';

    public function label(): string
    {
        return match ($this) {
            self::Visible => 'Visible',
            self::Hidden => 'Masqué',
            self::GreyedOut => 'Grisé',
        };
    }
}
