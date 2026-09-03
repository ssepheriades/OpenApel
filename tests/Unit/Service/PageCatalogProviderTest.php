<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\PageView;
use App\Entity\Page;
use App\Enum\PageSlug;
use App\Repository\PageRepository;
use App\Service\PageCatalogProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class PageCatalogProviderTest extends TestCase
{
    public function testAllIsCachedUntilInvalidated(): void
    {
        $pages = [];
        foreach (PageSlug::cases() as $slug) {
            $pages[] = Page::fromSlug($slug);
        }

        $repository = $this->createMock(PageRepository::class);
        $repository->expects(self::exactly(2))->method('ensureCatalog')->willReturn($pages);

        $provider = new PageCatalogProvider($repository, new ArrayAdapter());

        $first = $provider->all();
        $second = $provider->all();

        self::assertCount(\count(PageSlug::cases()), $first);
        self::assertInstanceOf(PageView::class, $first[0]);
        self::assertEquals($first, $second);
        self::assertSame('home', $provider->get('home')?->slug);

        $provider->invalidate();
        $provider->all();
    }
}
