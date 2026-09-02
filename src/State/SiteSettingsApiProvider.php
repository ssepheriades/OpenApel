<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\SiteSettingsResource;
use App\Service\SiteSettingsProvider;
use Symfony\Component\HttpFoundation\UrlHelper;

/**
 * @implements ProviderInterface<SiteSettingsResource>
 */
final class SiteSettingsApiProvider implements ProviderInterface
{
    public function __construct(
        private readonly SiteSettingsProvider $settingsProvider,
        private readonly UrlHelper $urlHelper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): SiteSettingsResource
    {
        $settings = $this->settingsProvider->get();

        return new SiteSettingsResource(
            siteName: $settings->siteName,
            baseline: $settings->baseline,
            homeTitle: $settings->homeTitle,
            homeText: $settings->homeText,
            logoUrl: $this->absolute($settings->logoUrl),
            faviconUrl: $this->absolute($settings->faviconUrl),
            contactEmail: $settings->contactEmail,
            facebookUrl: $settings->facebookUrl,
            instagramUrl: $settings->instagramUrl,
            primaryColor: $settings->primaryColor,
            secondaryColor: $settings->secondaryColor,
            schoolYearStart: $settings->schoolYearStart,
            schoolYearEnd: $settings->schoolYearEnd,
            faqVisible: $settings->faqVisible,
            teamVisible: $settings->teamVisible,
            postsVisible: $settings->postsVisible,
            agendaVisible: $settings->agendaVisible,
        );
    }

    private function absolute(?string $path): ?string
    {
        return null === $path ? null : $this->urlHelper->getAbsoluteUrl($path);
    }
}
