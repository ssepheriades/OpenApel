<?php

declare(strict_types=1);

namespace App\Enum;

enum EventType: string
{
    case PublicHoliday = 'public_holiday';
    case Vacation = 'vacation';
    case PedagogicalDay = 'pedagogical_day';
    case Party = 'party';
    case PublicMeeting = 'public_meeting';
    case SchoolEvent = 'school_event';

    public function label(): string
    {
        return match ($this) {
            self::PublicHoliday => 'Jour férié',
            self::Vacation => 'Vacances',
            self::PedagogicalDay => 'Journée pédagogique',
            self::Party => 'Fête',
            self::PublicMeeting => 'Réunion publique',
            self::SchoolEvent => 'Événement scolaire',
        };
    }

    /**
     * Types parents can subscribe to or add to a personal calendar.
     * Vacations and public holidays are omitted to avoid duplicates with national calendars.
     */
    public function isCalendarExportable(): bool
    {
        return match ($this) {
            self::PublicHoliday, self::Vacation => false,
            self::PedagogicalDay, self::Party, self::PublicMeeting, self::SchoolEvent => true,
        };
    }

    /**
     * @return list<self>
     */
    public static function calendarExportableCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $type): bool => $type->isCalendarExportable(),
        ));
    }
}
