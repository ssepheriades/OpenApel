<?php

declare(strict_types=1);

namespace App\Twig;

use App\Dto\SiteSettingsView;
use App\Service\SiteSettingsProvider;
use Twig\Extension\RuntimeExtensionInterface;

final class SiteSettingsRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly SiteSettingsProvider $provider,
    ) {
    }

    public function getSettings(): SiteSettingsView
    {
        return $this->provider->get();
    }
}
