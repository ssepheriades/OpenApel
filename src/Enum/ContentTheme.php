<?php

declare(strict_types=1);

namespace App\Enum;

enum ContentTheme: string
{
    case Apprentissage = 'apprentissage';
    case Sport = 'sport';
    case Culture = 'culture';
    case Alimentation = 'alimentation';
    case ExtraScolaire = 'extra_scolaire';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Apprentissage => 'Apprentissage',
            self::Sport => 'Sport',
            self::Culture => 'Culture',
            self::Alimentation => 'Alimentation',
            self::ExtraScolaire => 'Extra scolaire',
            self::Autre => 'Autre',
        };
    }
}
