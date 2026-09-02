<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\ContentTheme;
use PHPUnit\Framework\TestCase;

final class ContentThemeTest extends TestCase
{
    public function testCasesKeepTheSharedStringValues(): void
    {
        self::assertSame(
            [
                'apprentissage',
                'sport',
                'culture',
                'alimentation',
                'extra_scolaire',
                'autre',
            ],
            array_map(static fn (ContentTheme $theme): string => $theme->value, ContentTheme::cases()),
        );
    }

    public function testLabelsAreFrench(): void
    {
        self::assertSame('Apprentissage', ContentTheme::Apprentissage->label());
        self::assertSame('Sport', ContentTheme::Sport->label());
        self::assertSame('Culture', ContentTheme::Culture->label());
        self::assertSame('Alimentation', ContentTheme::Alimentation->label());
        self::assertSame('Extra scolaire', ContentTheme::ExtraScolaire->label());
        self::assertSame('Autre', ContentTheme::Autre->label());
    }
}
