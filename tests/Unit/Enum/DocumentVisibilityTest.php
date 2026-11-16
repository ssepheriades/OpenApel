<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\DocumentVisibility;
use PHPUnit\Framework\TestCase;

final class DocumentVisibilityTest extends TestCase
{
    public function testCases(): void
    {
        $values = array_map(
            static fn (DocumentVisibility $visibility): string => $visibility->value,
            DocumentVisibility::cases(),
        );

        self::assertSame(['visible', 'hidden', 'archived'], $values);
    }

    public function testLabels(): void
    {
        self::assertSame('Visible', DocumentVisibility::Visible->label());
        self::assertSame('Masqué', DocumentVisibility::Hidden->label());
        self::assertSame('Archivé', DocumentVisibility::Archived->label());
    }

    public function testOnlyVisibleIsListed(): void
    {
        self::assertTrue(DocumentVisibility::Visible->isListed());
        self::assertFalse(DocumentVisibility::Hidden->isListed());
        self::assertFalse(DocumentVisibility::Archived->isListed());
    }

    public function testArchivedIsNotPubliclyDownloadable(): void
    {
        self::assertTrue(DocumentVisibility::Visible->isPubliclyDownloadable());
        self::assertTrue(DocumentVisibility::Hidden->isPubliclyDownloadable());
        self::assertFalse(DocumentVisibility::Archived->isPubliclyDownloadable());
    }
}
