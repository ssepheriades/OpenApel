<?php

declare(strict_types=1);

namespace App\Service;

use App\AppTimezone;

/**
 * Recurring school-year bounds (month and day only; the stored year is ignored).
 */
final class SchoolYear
{
    /**
     * Inclusive start of the school year that contains $now, at midnight Europe/Paris.
     */
    public function currentStart(
        \DateTimeImmutable $now,
        \DateTimeImmutable $startMonthDay,
        \DateTimeImmutable $endMonthDay,
    ): \DateTimeImmutable {
        $tz = new \DateTimeZone(AppTimezone::NAME);
        $nowLocal = $now->setTimezone($tz);
        $year = (int) $nowLocal->format('Y');
        $nowValue = $this->monthDayValue($nowLocal);
        $startValue = $this->monthDayValue($startMonthDay);
        $endValue = $this->monthDayValue($endMonthDay);
        $wrapsCalendarYear = $startValue > $endValue;

        if (!$wrapsCalendarYear) {
            return $this->atMidnight($year, $startMonthDay, $tz);
        }

        if ($nowValue >= $startValue) {
            return $this->atMidnight($year, $startMonthDay, $tz);
        }

        return $this->atMidnight($year - 1, $startMonthDay, $tz);
    }

    private function monthDayValue(\DateTimeImmutable $date): int
    {
        return ((int) $date->format('n')) * 100 + (int) $date->format('j');
    }

    private function atMidnight(int $year, \DateTimeImmutable $monthDay, \DateTimeZone $tz): \DateTimeImmutable
    {
        return new \DateTimeImmutable(
            sprintf('%04d-%s 00:00:00', $year, $monthDay->format('m-d')),
            $tz,
        );
    }
}
