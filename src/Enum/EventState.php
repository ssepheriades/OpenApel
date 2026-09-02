<?php

declare(strict_types=1);

namespace App\Enum;

enum EventState: string
{
    case Open = 'open';
    case Full = 'full';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Ouvert',
            self::Full => 'Complet',
            self::Cancelled => 'Annulé',
        };
    }
}
