<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\MediaMapping;
use PHPUnit\Framework\TestCase;

final class MediaMappingTest extends TestCase
{
    public function testPublicMappingsExposeMediaUrls(): void
    {
        self::assertSame('/media/photos', MediaMapping::Photos->uriPrefix());
        self::assertSame('/media/photos/hero.webp', MediaMapping::Photos->url('hero.webp'));
        self::assertSame('/media/branding/logo.png', MediaMapping::Branding->url('logo.png'));
        self::assertNull(MediaMapping::Photos->url(null));
        self::assertNull(MediaMapping::Photos->url(''));
    }

    public function testDocumentsArePublic(): void
    {
        self::assertTrue(MediaMapping::Photos->isPublic());
        self::assertTrue(MediaMapping::Branding->isPublic());
        self::assertTrue(MediaMapping::Documents->isPublic());
        self::assertSame('/media/documents/compte-rendu.pdf', MediaMapping::Documents->url('compte-rendu.pdf'));
    }
}
