<?php

declare(strict_types=1);

namespace App\Enum;

enum FaqVisibility: string
{
    case Visible = 'visible';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Visible => 'Visible',
            self::Hidden => 'Masqué',
        };
    }
}
