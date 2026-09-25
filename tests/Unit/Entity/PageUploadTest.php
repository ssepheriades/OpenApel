<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Page;
use App\Enum\MediaMapping;
use App\Enum\PageSlug;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

final class PageUploadTest extends TestCase
{
    public function testCoverImageDirtiesUpdatedAtAndExposesUrl(): void
    {
        $page = Page::fromSlug(PageSlug::News);
        self::assertNull($page->getUpdatedAt());
        self::assertNull($page->getCoverImageUrl());

        $page->setCoverImageFile(new File(__FILE__, false));
        $page->setCoverImageFilename('bandeau.webp');

        self::assertInstanceOf(\DateTimeImmutable::class, $page->getUpdatedAt());
        self::assertSame(MediaMapping::Photos->url('bandeau.webp'), $page->getCoverImageUrl());
    }
}
