<?php

declare(strict_types=1);

namespace App\Enum;

enum PostState: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Published => 'Publié',
        };
    }
}
