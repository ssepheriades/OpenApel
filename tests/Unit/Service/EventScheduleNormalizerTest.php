<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\AppTimezone;
use App\Entity\Event;
use App\Service\EventScheduleNormalizer;
use PHPUnit\Framework\TestCase;

final class EventScheduleNormalizerTest extends TestCase
{
    private EventScheduleNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new EventScheduleNormalizer();
    }

    public function testAllDayStartIsMidnightParis(): void
    {
        $event = (new Event())
            ->setIsAllDay(true)
            ->setStartsAt(new \DateTimeImmutable('2026-09-12 14:30:00', $this->paris()));

        $this->normalizer->normalize($event);

        self::assertSame('2026-09-12 00:00:00', $event->getStartsAt()?->format('Y-m-d H:i:s'));
        self::assertSame(AppTimezone::NAME, $event->getStartsAt()?->getTimezone()->getName());
        self::assertNull($event->getEndsAt());
    }

    public function testSameDayEndBecomesNull(): void
    {
        $event = (new Event())
            ->setIsAllDay(true)
            ->setStartsAt(new \DateTimeImmutable('2026-09-12 08:00:00', $this->paris()))
            ->setEndsAt(new \DateTimeImmutable('2026-09-12 18:00:00', $this->paris()));

        $this->normalizer->normalize($event);

        self::assertSame('2026-09-12 00:00:00', $event->getStartsAt()?->format('Y-m-d H:i:s'));
        self::assertNull($event->getEndsAt());
    }

    public function testInclusiveMultiDayEndKeepsLastMidnight(): void
    {
        $event = (new Event())
            ->setIsAllDay(true)
            ->setStartsAt(new \DateTimeImmutable('2026-09-12 14:30:00', $this->paris()))
            ->setEndsAt(new \DateTimeImmutable('2026-09-16 18:00:00', $this->paris()));

        $this->normalizer->normalize($event);

        self::assertSame('2026-09-12 00:00:00', $event->getStartsAt()?->format('Y-m-d H:i:s'));
        self::assertSame('2026-09-16 00:00:00', $event->getEndsAt()?->format('Y-m-d H:i:s'));
    }

    public function testEarlierEndDayIsKeptSoValidationCanFail(): void
    {
        $event = (new Event())
            ->setIsAllDay(true)
            ->setStartsAt(new \DateTimeImmutable('2026-09-16 14:00:00', $this->paris()))
            ->setEndsAt(new \DateTimeImmutable('2026-09-12 18:00:00', $this->paris()));

        $this->normalizer->normalize($event);

        self::assertSame('2026-09-16 00:00:00', $event->getStartsAt()?->format('Y-m-d H:i:s'));
        self::assertSame('2026-09-12 00:00:00', $event->getEndsAt()?->format('Y-m-d H:i:s'));
    }

    public function testTimedEventsAreUnchanged(): void
    {
        $start = new \DateTimeImmutable('2026-09-12 18:00:00', $this->paris());
        $end = new \DateTimeImmutable('2026-09-12 20:30:00', $this->paris());
        $event = (new Event())
            ->setIsAllDay(false)
            ->setStartsAt($start)
            ->setEndsAt($end);

        $this->normalizer->normalize($event);

        self::assertSame($start, $event->getStartsAt());
        self::assertSame($end, $event->getEndsAt());
    }

    private function paris(): \DateTimeZone
    {
        return new \DateTimeZone(AppTimezone::NAME);
    }
}
