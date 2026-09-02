<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SiteSettingsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        // Runtime keeps the DB/cache lookup lazy: not every template needs the settings.
        return [
            new TwigFunction('site_settings', [SiteSettingsRuntime::class, 'getSettings']),
        ];
    }
}
