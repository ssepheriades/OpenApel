<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\PageView;
use App\Enum\PageSlug;
use App\Repository\PageRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;

final class PageCatalogProvider
{
    public const string CACHE_KEY = 'page_catalog';

    public function __construct(
        private readonly PageRepository $repository,
        #[Autowire(service: 'page_catalog.cache')]
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * Catalogue in enum order. Never returns managed entities.
     *
     * @return list<PageView>
     */
    public function all(): array
    {
        return $this->cache->get(
            self::CACHE_KEY,
            function (): array {
                $views = [];
                foreach ($this->repository->ensureCatalog() as $page) {
                    $views[] = PageView::fromEntity($page);
                }

                return $views;
            },
        );
    }

    public function get(string $slug): ?PageView
    {
        foreach ($this->all() as $page) {
            if ($page->slug === $slug) {
                return $page;
            }
        }

        return null;
    }

    public function getBySlug(PageSlug $slug): PageView
    {
        $page = $this->get($slug->value);
        if (null === $page) {
            throw new \LogicException(sprintf('Catalogue page "%s" is missing after ensureCatalog().', $slug->value));
        }

        return $page;
    }

    public function invalidate(): void
    {
        $this->cache->delete(self::CACHE_KEY);
    }
}
