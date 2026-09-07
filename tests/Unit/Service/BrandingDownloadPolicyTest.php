<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\SiteSettings;
use App\Enum\MediaDownloadAccess;
use App\Repository\SiteSettingsRepository;
use App\Service\BrandingDownloadPolicy;
use App\Service\SiteSettingsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class BrandingDownloadPolicyTest extends TestCase
{
    public function testCurrentLogoIsPublic(): void
    {
        $policy = $this->policy((new SiteSettings())->setLogoFilename('logo.png'));

        self::assertSame(MediaDownloadAccess::Public, $policy->access('logo.png'));
    }

    public function testCurrentFaviconIsPublic(): void
    {
        $policy = $this->policy((new SiteSettings())->setFaviconFilename('favicon.ico'));

        self::assertSame(MediaDownloadAccess::Public, $policy->access('favicon.ico'));
    }

    public function testUnknownBrandingFileIsDenied(): void
    {
        $policy = $this->policy((new SiteSettings())->setLogoFilename('logo.png'));

        self::assertSame(MediaDownloadAccess::Denied, $policy->access('old-logo.png'));
    }

    public function testEmptyBrandingIsDenied(): void
    {
        $policy = $this->policy(new SiteSettings());

        self::assertSame(MediaDownloadAccess::Denied, $policy->access('logo.png'));
    }

    private function policy(SiteSettings $settings): BrandingDownloadPolicy
    {
        $repository = $this->createMock(SiteSettingsRepository::class);
        $repository->method('getOrCreate')->willReturn($settings);

        return new BrandingDownloadPolicy(new SiteSettingsProvider($repository, new ArrayAdapter()));
    }
}
