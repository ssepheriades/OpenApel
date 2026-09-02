<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\SiteSettingsView;
use App\Entity\SiteSettings;
use App\Repository\SiteSettingsRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;

final class SiteSettingsProvider
{
    public const string CACHE_KEY = 'site_settings';

    public function __construct(
        private readonly SiteSettingsRepository $repository,
        #[Autowire(service: 'site_settings.cache')]
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * Cached snapshot for read paths (Twig, API, dashboard). Never returns the managed entity.
     */
    public function get(): SiteSettingsView
    {
        return $this->cache->get(
            self::CACHE_KEY,
            fn (): SiteSettingsView => SiteSettingsView::fromEntity($this->repository->getOrCreate()),
        );
    }

    /**
     * Uncached managed entity, for the back-office edit form only.
     */
    public function getEntity(): SiteSettings
    {
        return $this->repository->getOrCreate();
    }

    public function invalidate(): void
    {
        $this->cache->delete(self::CACHE_KEY);
    }
}
