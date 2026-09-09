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
}
