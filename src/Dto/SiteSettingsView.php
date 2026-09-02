<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\SiteSettings;

/**
 * Cache-friendly, read-only snapshot of the site settings.
 * URLs are relative to the document root; the API provider makes them absolute.
 */
final readonly class SiteSettingsView
{
    public const string UPLOAD_URI_PREFIX = '/uploads/branding/';

    public function __construct(
        public string $siteName,
        public ?string $baseline,
        public ?string $homeTitle,
        public ?string $homeText,
        public ?string $logoUrl,
        public ?string $faviconUrl,
        public ?string $contactEmail,
        public bool $contactEmailEnabled,
        public ?string $facebookUrl,
        public ?string $instagramUrl,
        public string $primaryColor,
        public string $secondaryColor,
        public string $schoolYearStart,
        public string $schoolYearEnd,
        public bool $faqVisible,
        public bool $teamVisible,
        public bool $postsVisible,
        public bool $agendaVisible,
    ) {
    }

    public static function fromEntity(SiteSettings $settings): self
    {
        return new self(
            siteName: $settings->getSiteName(),
            baseline: $settings->getBaseline(),
            homeTitle: $settings->getHomeTitle(),
            homeText: $settings->getHomeText(),
            logoUrl: self::uploadUrl($settings->getLogoFilename()),
            faviconUrl: self::uploadUrl($settings->getFaviconFilename()),
            contactEmail: $settings->getContactEmail(),
            contactEmailEnabled: $settings->isContactEmailEnabled(),
            facebookUrl: $settings->getFacebookUrl(),
            instagramUrl: $settings->getInstagramUrl(),
            primaryColor: $settings->getPrimaryColor(),
            secondaryColor: $settings->getSecondaryColor(),
            schoolYearStart: $settings->getSchoolYearStart()->format('Y-m-d'),
            schoolYearEnd: $settings->getSchoolYearEnd()->format('Y-m-d'),
            faqVisible: $settings->isFaqVisible(),
            teamVisible: $settings->isTeamVisible(),
            postsVisible: $settings->isPostsVisible(),
            agendaVisible: $settings->isAgendaVisible(),
        );
    }

    private static function uploadUrl(?string $filename): ?string
    {
        return null === $filename ? null : self::UPLOAD_URI_PREFIX . $filename;
    }
}
