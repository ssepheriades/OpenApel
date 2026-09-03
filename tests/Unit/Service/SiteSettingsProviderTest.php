<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\SiteSettingsView;
use App\Entity\SiteSettings;
use App\Repository\SiteSettingsRepository;
use App\Service\SiteSettingsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class SiteSettingsProviderTest extends TestCase
{
    public function testGetIsCachedUntilInvalidated(): void
    {
        $settings = (new SiteSettings())->setSiteName('APEL Test')->setLogoFilename('logo.png');

        $repository = $this->createMock(SiteSettingsRepository::class);
        $repository->expects(self::exactly(2))->method('getOrCreate')->willReturn($settings);

        $provider = new SiteSettingsProvider($repository, new ArrayAdapter());

        $first = $provider->get();
        $second = $provider->get();

        self::assertInstanceOf(SiteSettingsView::class, $first);
        self::assertSame('APEL Test', $first->siteName);
        self::assertSame('/uploads/branding/logo.png', $first->logoUrl);
        self::assertNull($first->faviconUrl);
        self::assertEquals($first, $second);

        $provider->invalidate();
        $provider->get();
    }

    public function testViewExposesDefaults(): void
    {
        $view = SiteSettingsView::fromEntity(new SiteSettings());

        self::assertSame(SiteSettings::DEFAULT_SITE_NAME, $view->siteName);
        self::assertSame(SiteSettings::DEFAULT_PRIMARY_COLOR, $view->primaryColor);
        self::assertSame(SiteSettings::DEFAULT_SECONDARY_COLOR, $view->secondaryColor);
        self::assertSame(SiteSettings::DEFAULT_SCHOOL_YEAR_START, $view->schoolYearStart);
        self::assertSame(SiteSettings::DEFAULT_SCHOOL_YEAR_END, $view->schoolYearEnd);
        self::assertTrue($view->contactEmailEnabled);
        self::assertNull($view->baseline);
        self::assertNull($view->logoUrl);
    }
}
