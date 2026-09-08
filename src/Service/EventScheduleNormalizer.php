<?php

declare(strict_types=1);

namespace App\Service;

use App\AppTimezone;
use App\Entity\Event;

/**
 * All-day events are stored as midnight Europe/Paris on each civil day.
 * A missing or same-day end is persisted as null (single-day all-day).
 */
final class EventScheduleNormalizer
{
    public function normalize(Event $event): void
    {
        if (!$event->isAllDay()) {
            return;
        }

        $startsAt = $event->getStartsAt();
        if (null === $startsAt) {
            return;
        }

        $start = $this->atMidnight($startsAt);
        $event->setStartsAt($start);

        $endsAt = $event->getEndsAt();
        if (null === $endsAt) {
            return;
        }

        $end = $this->atMidnight($endsAt);
        if ($end == $start) {
            $event->setEndsAt(null);

            return;
        }

        $event->setEndsAt($end);
    }

    private function atMidnight(\DateTimeImmutable $date): \DateTimeImmutable
    {
        $tz = new \DateTimeZone(AppTimezone::NAME);
        $local = $date->setTimezone($tz);

        return new \DateTimeImmutable($local->format('Y-m-d') . ' 00:00:00', $tz);
    }
}
