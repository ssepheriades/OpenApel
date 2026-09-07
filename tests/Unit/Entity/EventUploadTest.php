<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

final class EventUploadTest extends TestCase
{
    public function testSettingHeroOrFlyerDirtiesUpdatedAt(): void
    {
        $event = new Event();
        self::assertNull($event->getUpdatedAt());

        $event->setHeroImageFile(new File(__FILE__, false));
        $heroTouchedAt = $event->getUpdatedAt();
        self::assertInstanceOf(\DateTimeImmutable::class, $heroTouchedAt);

        $event->setFlyerImageFile(new File(__FILE__, false));
        self::assertInstanceOf(\DateTimeImmutable::class, $event->getUpdatedAt());
        self::assertGreaterThanOrEqual($heroTouchedAt, $event->getUpdatedAt());
    }
}
