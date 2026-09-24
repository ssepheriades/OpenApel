<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\EventType;
use PHPUnit\Framework\TestCase;

final class EventTypeTest extends TestCase
{
    public function testCasesIncludePedagogicalDay(): void
    {
        $values = array_map(static fn (EventType $type): string => $type->value, EventType::cases());

        self::assertSame(
            [
                'public_holiday',
                'vacation',
                'pedagogical_day',
                'party',
                'public_meeting',
                'school_event',
            ],
            $values,
        );
    }

    public function testPedagogicalDayLabel(): void
    {
        self::assertSame('Journée pédagogique', EventType::PedagogicalDay->label());
    }

    public function testCalendarExportableTypesExcludeNationalClosures(): void
    {
        self::assertFalse(EventType::PublicHoliday->isCalendarExportable());
        self::assertFalse(EventType::Vacation->isCalendarExportable());
        self::assertTrue(EventType::PedagogicalDay->isCalendarExportable());
        self::assertTrue(EventType::Party->isCalendarExportable());
        self::assertTrue(EventType::PublicMeeting->isCalendarExportable());
        self::assertTrue(EventType::SchoolEvent->isCalendarExportable());
        self::assertSame(
            [
                EventType::PedagogicalDay,
                EventType::Party,
                EventType::PublicMeeting,
                EventType::SchoolEvent,
            ],
            EventType::calendarExportableCases(),
        );
    }
}
