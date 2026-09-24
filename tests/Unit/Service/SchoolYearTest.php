<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\AppTimezone;
use App\Service\SchoolYear;
use PHPUnit\Framework\TestCase;

final class SchoolYearTest extends TestCase
{
    private SchoolYear $schoolYear;

    protected function setUp(): void
    {
        $this->schoolYear = new SchoolYear();
    }

    public function testStartsANewYearOnFirstAugust(): void
    {
        $start = $this->schoolYear->currentStart(
            $this->paris('2026-08-01 00:00:00'),
            new \DateTimeImmutable('2000-08-01'),
            new \DateTimeImmutable('2000-07-31'),
        );

        self::assertSame('2026-08-01 00:00:00', $start->format('Y-m-d H:i:s'));
        self::assertSame(AppTimezone::NAME, $start->getTimezone()->getName());
    }

    public function testKeepsSeptemberInsideTheYearThatStartedInAugust(): void
    {
        $start = $this->schoolYear->currentStart(
            $this->paris('2026-09-01 12:00:00'),
            new \DateTimeImmutable('2000-08-01'),
            new \DateTimeImmutable('2000-07-31'),
        );

        self::assertSame('2026-08-01 00:00:00', $start->format('Y-m-d H:i:s'));
    }

    public function testUsesPreviousFirstAugustWhenNowIsThirtyFirstJuly(): void
    {
        $start = $this->schoolYear->currentStart(
            $this->paris('2026-07-31 23:59:59'),
            new \DateTimeImmutable('2000-08-01'),
            new \DateTimeImmutable('2000-07-31'),
        );

        self::assertSame('2025-08-01 00:00:00', $start->format('Y-m-d H:i:s'));
    }

    public function testKeepsANonWrappingRangeInTheSameCalendarYear(): void
    {
        $start = $this->schoolYear->currentStart(
            $this->paris('2026-06-15 08:00:00'),
            new \DateTimeImmutable('2000-01-01'),
            new \DateTimeImmutable('2000-12-31'),
        );

        self::assertSame('2026-01-01 00:00:00', $start->format('Y-m-d H:i:s'));
    }

    private function paris(string $datetime): \DateTimeImmutable
    {
        return new \DateTimeImmutable($datetime, new \DateTimeZone(AppTimezone::NAME));
    }
}
