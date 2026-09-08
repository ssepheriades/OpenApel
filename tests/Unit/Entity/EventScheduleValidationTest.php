<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\AppTimezone;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class EventScheduleValidationTest extends TestCase
{
    public function testAllDayEndBeforeStartIsInvalid(): void
    {
        $paris = new \DateTimeZone(AppTimezone::NAME);
        $event = (new Event())
            ->setIsAllDay(true)
            ->setStartsAt(new \DateTimeImmutable('2026-09-16 00:00:00', $paris))
            ->setEndsAt(new \DateTimeImmutable('2026-09-12 00:00:00', $paris));

        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violation->expects(self::once())->method('atPath')->with('allDayEndsOn')->willReturnSelf();
        $violation->expects(self::once())->method('addViolation');

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects(self::once())->method('buildViolation')->willReturn($violation);

        $event->validateSchedule($context);
    }

    public function testTimedEndBeforeStartIsInvalid(): void
    {
        $paris = new \DateTimeZone(AppTimezone::NAME);
        $event = (new Event())
            ->setIsAllDay(false)
            ->setStartsAt(new \DateTimeImmutable('2026-09-12 20:00:00', $paris))
            ->setEndsAt(new \DateTimeImmutable('2026-09-12 18:00:00', $paris));

        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violation->expects(self::once())->method('atPath')->with('endsAt')->willReturnSelf();
        $violation->expects(self::once())->method('addViolation');

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects(self::once())->method('buildViolation')->willReturn($violation);

        $event->validateSchedule($context);
    }
}
