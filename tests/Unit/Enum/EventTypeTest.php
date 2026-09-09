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
}
