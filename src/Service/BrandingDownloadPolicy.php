<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\MediaDownloadAccess;
use App\Enum\MediaMapping;

/**
 * Whether GET /media/branding/{filename} may serve the file.
 * Only the current SiteSettings logo or favicon is public; replaced/orphan files 404 for everyone.
 */
final readonly class BrandingDownloadPolicy
{
    public function __construct(
        private SiteSettingsProvider $siteSettingsProvider,
    ) {
    }

    public function access(string $filename): MediaDownloadAccess
    {
        $url = MediaMapping::Branding->url($filename);
        $settings = $this->siteSettingsProvider->get();

        if ($url === $settings->logoUrl || $url === $settings->faviconUrl) {
            return MediaDownloadAccess::Public;
        }

        return MediaDownloadAccess::Denied;
    }
}
