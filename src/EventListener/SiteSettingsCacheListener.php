<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\SiteSettings;
use App\Service\SiteSettingsProvider;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Settings are only ever edited through EasyAdmin, so its lifecycle events
 * are the single place where the cached snapshot must be dropped.
 */
final class SiteSettingsCacheListener
{
    public function __construct(
        private readonly SiteSettingsProvider $provider,
    ) {
    }

    #[AsEventListener]
    public function onUpdated(AfterEntityUpdatedEvent $event): void
    {
        $this->invalidateIfSettings($event->getEntityInstance());
    }

    #[AsEventListener]
    public function onPersisted(AfterEntityPersistedEvent $event): void
    {
        $this->invalidateIfSettings($event->getEntityInstance());
    }

    private function invalidateIfSettings(mixed $entity): void
    {
        if ($entity instanceof SiteSettings) {
            $this->provider->invalidate();
        }
    }
}
