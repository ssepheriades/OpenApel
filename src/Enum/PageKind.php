<?php

declare(strict_types=1);

namespace App\Enum;

enum PageKind: string
{
    case Section = 'section';
    case Document = 'document';

    public function label(): string
    {
        return match ($this) {
            self::Section => 'Rubrique',
            self::Document => 'Page',
        };
    }
}
