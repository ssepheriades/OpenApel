<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\SiteSettingsApiProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'SiteSettings',
    operations: [
        new Get(
            uriTemplate: '/site_settings',
            // Keep null keys: the SPA relies on a stable shape (logoUrl: null, not missing).
            normalizationContext: ['groups' => ['site_settings:read'], 'skip_null_values' => false],
            provider: SiteSettingsApiProvider::class,
        ),
    ],
)]
final readonly class SiteSettingsResource
{
    public function __construct(
        #[Groups(['site_settings:read'])]
        public string $siteName,
        #[Groups(['site_settings:read'])]
        public ?string $baseline,
        #[Groups(['site_settings:read'])]
        public ?string $logoUrl,
        #[Groups(['site_settings:read'])]
        public ?string $faviconUrl,
        #[Groups(['site_settings:read'])]
        public ?string $contactEmail,
        #[Groups(['site_settings:read'])]
        public ?string $facebookUrl,
        #[Groups(['site_settings:read'])]
        public ?string $instagramUrl,
        #[Groups(['site_settings:read'])]
        public string $primaryColor,
        #[Groups(['site_settings:read'])]
        public string $secondaryColor,
        #[Groups(['site_settings:read'])]
        public string $schoolYearStart,
        #[Groups(['site_settings:read'])]
        public string $schoolYearEnd,
        // Constant identifier: a singleton still needs an @id for JSON-LD.
        #[ApiProperty(identifier: true)]
        #[Groups(['site_settings:read'])]
        public string $id = 'site_settings',
    ) {
    }
}
