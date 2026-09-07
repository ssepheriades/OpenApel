<?php

declare(strict_types=1);

namespace App\Tests\Unit\ApiResource;

use App\ApiResource\PageResource;
use App\Dto\PageView;
use PHPUnit\Framework\TestCase;

final class PageResourceTest extends TestCase
{
    public function testFromViewKeepsBodyWhenVisible(): void
    {
        $resource = PageResource::fromView(new PageView(
            slug: 'mentions-legales',
            kind: 'document',
            title: 'Mentions légales',
            subtitle: null,
            body: 'Contenu public',
            visible: true,
        ));

        self::assertSame('Contenu public', $resource->body);
        self::assertTrue($resource->visible);
    }

    public function testFromViewStripsBodyWhenHidden(): void
    {
        $resource = PageResource::fromView(new PageView(
            slug: 'mentions-legales',
            kind: 'document',
            title: 'Mentions légales',
            subtitle: 'Chapô',
            body: 'Brouillon confidentiel',
            visible: false,
        ));

        self::assertNull($resource->body);
        self::assertFalse($resource->visible);
        self::assertSame('Mentions légales', $resource->title);
        self::assertSame('Chapô', $resource->subtitle);
    }
}
