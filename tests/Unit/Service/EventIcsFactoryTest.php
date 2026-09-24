<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\AppTimezone;
use App\Entity\Event;
use App\Enum\EventState;
use App\Enum\EventType;
use App\Enum\EventVisibility;
use App\Service\EventIcsFactory;
use App\Service\IcsEncoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EventIcsFactoryTest extends TestCase
{
    private EventIcsFactory $factory;

    protected function setUp(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(
            static fn (string $name, array $params): string => 'http://localhost/'.$params['reactRouting'],
        );

        $this->factory = new EventIcsFactory(new IcsEncoder(), $urlGenerator, 'test');
    }

    public function testTimedEventIsExportedInUtc(): void
    {
        $event = $this->event()
            ->setStartsAt($this->paris('2026-09-12 18:00:00'))
            ->setEndsAt($this->paris('2026-09-12 20:30:00'));

        $ics = $this->factory->renderEvent($event, $this->utc('2026-09-10 10:00:00'));

        self::assertStringContainsString("BEGIN:VCALENDAR\r\n", $ics);
        self::assertStringContainsString('UID:event-42@test.openapel', $ics);
        self::assertStringContainsString('DTSTART:20260912T160000Z', $ics);
        self::assertStringContainsString('DTEND:20260912T183000Z', $ics);
        self::assertStringContainsString('SUMMARY:Assemblée générale', $ics);
        self::assertStringContainsString('STATUS:CONFIRMED', $ics);
        self::assertStringContainsString('LOCATION:Salle des fêtes', $ics);
        self::assertStringContainsString('URL:http://localhost/agenda/assemblee-generale', $ics);
        self::assertStringContainsString('CATEGORIES:Réunion publique', $ics);
        self::assertStringNotContainsString('METHOD:PUBLISH', $ics);
    }

    public function testAllDayUsesExclusiveDateEnd(): void
    {
        $event = $this->event()
            ->setIsAllDay(true)
            ->setStartsAt($this->paris('2026-09-12 00:00:00'))
            ->setEndsAt($this->paris('2026-09-16 00:00:00'));

        $ics = $this->factory->renderEvent($event, $this->utc('2026-09-10 10:00:00'));

        self::assertStringContainsString('DTSTART;VALUE=DATE:20260912', $ics);
        self::assertStringContainsString('DTEND;VALUE=DATE:20260917', $ics);
    }

    public function testSingleDayAllDayDefaultsExclusiveEndToNextDay(): void
    {
        $event = $this->event()
            ->setIsAllDay(true)
            ->setStartsAt($this->paris('2026-09-12 00:00:00'))
            ->setEndsAt(null);

        $ics = $this->factory->renderEvent($event, $this->utc('2026-09-10 10:00:00'));

        self::assertStringContainsString('DTSTART;VALUE=DATE:20260912', $ics);
        self::assertStringContainsString('DTEND;VALUE=DATE:20260913', $ics);
    }

    public function testCancelledEventSetsStatusAndSummaryPrefix(): void
    {
        $event = $this->event()->setState(EventState::Cancelled);

        $ics = $this->factory->renderEvent($event, $this->utc('2026-09-10 10:00:00'));

        self::assertStringContainsString('STATUS:CANCELLED', $ics);
        self::assertStringContainsString('SUMMARY:Annulé : Assemblée générale', $ics);
    }

    public function testFeedIncludesCalendarNameAndPublishMethod(): void
    {
        $event = $this->event();
        $ics = $this->factory->renderFeed('APE École Test', [$event], $this->utc('2026-09-10 10:00:00'));

        self::assertStringContainsString('METHOD:PUBLISH', $ics);
        self::assertStringContainsString('X-WR-CALNAME:APE École Test — Agenda', $ics);
        self::assertStringContainsString('UID:event-42@test.openapel', $ics);
    }

    public function testTimedEventWithoutEndDefaultsToOneHour(): void
    {
        $event = $this->event()
            ->setStartsAt($this->paris('2026-09-12 18:00:00'))
            ->setEndsAt(null);

        $ics = $this->factory->renderEvent($event, $this->utc('2026-09-10 10:00:00'));

        self::assertStringContainsString('DTSTART:20260912T160000Z', $ics);
        self::assertStringContainsString('DTEND:20260912T170000Z', $ics);
    }

    private function event(): Event
    {
        $event = (new Event())
            ->setTitle('Assemblée générale')
            ->setSlug('assemblee-generale')
            ->setDescription('Réunion des **parents**.')
            ->setLocation('Salle des fêtes')
            ->setStartsAt($this->paris('2026-09-12 18:00:00'))
            ->setEndsAt($this->paris('2026-09-12 20:30:00'))
            ->setType(EventType::PublicMeeting)
            ->setState(EventState::Open)
            ->setVisibility(EventVisibility::Visible)
            ->setIsAllDay(false);
        $event->setUpdatedTimestamp();

        $id = new \ReflectionProperty(Event::class, 'id');
        $id->setValue($event, 42);

        return $event;
    }

    private function paris(string $datetime): \DateTimeImmutable
    {
        return new \DateTimeImmutable($datetime, new \DateTimeZone(AppTimezone::NAME));
    }

    private function utc(string $datetime): \DateTimeImmutable
    {
        return new \DateTimeImmutable($datetime, new \DateTimeZone('UTC'));
    }
}
