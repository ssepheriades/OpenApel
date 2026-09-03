<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Page;
use App\Service\PageCatalogProvider;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Pages are only ever edited through EasyAdmin, so its lifecycle events
 * are the single place where the cached catalogue must be dropped.
 */
final class PageCatalogCacheListener
{
    public function __construct(
        private readonly PageCatalogProvider $provider,
    ) {
    }

    #[AsEventListener]
    public function onUpdated(AfterEntityUpdatedEvent $event): void
    {
        $this->invalidateIfPage($event->getEntityInstance());
    }

    #[AsEventListener]
    public function onPersisted(AfterEntityPersistedEvent $event): void
    {
        $this->invalidateIfPage($event->getEntityInstance());
    }

    private function invalidateIfPage(mixed $entity): void
    {
        if ($entity instanceof Page) {
            $this->provider->invalidate();
        }
    }
}
