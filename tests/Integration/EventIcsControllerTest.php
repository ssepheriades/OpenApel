<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\AppTimezone;
use App\Entity\Event;
use App\Enum\EventState;
use App\Enum\EventType;
use App\Enum\EventVisibility;
use App\Service\SchoolYear;
use App\Service\SiteSettingsProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventIcsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    /** @var list<int> */
    private array $createdIds = [];

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $event = $this->entityManager->find(Event::class, $id);
            if (null !== $event) {
                $this->entityManager->remove($event);
            }
        }
        if ([] !== $this->createdIds) {
            $this->entityManager->flush();
        }

        parent::tearDown();
    }

    public function testFeedIncludesVisibleExportableEventsFromCurrentSchoolYear(): void
    {
        $bounds = $this->schoolYearBounds();
        $included = $this->persistEvent(
            'kermesse-ics-'.$this->suffix(),
            EventType::Party,
            EventVisibility::Visible,
            $bounds['inside'],
        );
        $tooEarly = $this->persistEvent(
            'too-early-ics-'.$this->suffix(),
            EventType::Party,
            EventVisibility::Visible,
            $bounds['before'],
        );
        $vacation = $this->persistEvent(
            'vacances-ics-'.$this->suffix(),
            EventType::Vacation,
            EventVisibility::Visible,
            $bounds['inside'],
        );
        $hidden = $this->persistEvent(
            'hidden-ics-'.$this->suffix(),
            EventType::Party,
            EventVisibility::Hidden,
            $bounds['inside'],
        );

        $this->client->request('GET', '/calendar.ics');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertSame('text/calendar; charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
        $body = (string) $response->getContent();
        self::assertStringContainsString('BEGIN:VCALENDAR', $body);
        self::assertStringContainsString('METHOD:PUBLISH', $body);
        self::assertStringContainsString('UID:event-'.$included->getId().'@test.openapel', $body);
        self::assertStringNotContainsString('UID:event-'.$tooEarly->getId().'@test.openapel', $body);
        self::assertStringNotContainsString('UID:event-'.$vacation->getId().'@test.openapel', $body);
        self::assertStringNotContainsString('UID:event-'.$hidden->getId().'@test.openapel', $body);
    }

    public function testSingleEventIcsIsDownloadable(): void
    {
        $event = $this->persistEvent(
            'ag-ics-'.$this->suffix(),
            EventType::PublicMeeting,
            EventVisibility::Visible,
            $this->paris('2026-09-12 18:00:00'),
        );

        $this->client->request('GET', '/calendar/events/'.$event->getSlug().'.ics');

        self::assertResponseIsSuccessful();
        $body = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('SUMMARY:Assemblée ICS', $body);
        self::assertStringContainsString('DTSTART:20260912T160000Z', $body);
        self::assertStringContainsString('URL:http://localhost/agenda/'.$event->getSlug(), $body);
        self::assertStringNotContainsString('METHOD:PUBLISH', $body);
    }

    public function testGreyedOutAndUnknownSlugsAreNotFound(): void
    {
        $greyed = $this->persistEvent(
            'greyed-ics-'.$this->suffix(),
            EventType::Party,
            EventVisibility::GreyedOut,
            $this->paris('2026-09-12 18:00:00'),
        );

        $this->client->request('GET', '/calendar/events/'.$greyed->getSlug().'.ics');
        self::assertResponseStatusCodeSame(404);

        $this->client->request('GET', '/calendar/events/inconnu-ics.ics');
        self::assertResponseStatusCodeSame(404);
    }

    public function testCancelledEventStaysInTheFeedWithCancelledStatus(): void
    {
        $event = $this->persistEvent(
            'annule-ics-'.$this->suffix(),
            EventType::SchoolEvent,
            EventVisibility::Visible,
            $this->schoolYearBounds()['inside'],
            EventState::Cancelled,
        );

        $this->client->request('GET', '/calendar.ics');

        self::assertResponseIsSuccessful();
        $body = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('UID:event-'.$event->getId().'@test.openapel', $body);
        self::assertStringContainsString('STATUS:CANCELLED', $body);
    }

    /**
     * @return array{inside: \DateTimeImmutable, before: \DateTimeImmutable}
     */
    private function schoolYearBounds(): array
    {
        try {
            $settings = static::getContainer()->get(SiteSettingsProvider::class)->get();
        } catch (\Throwable $exception) {
            self::markTestSkipped(sprintf('Database is not reachable for integration tests: %s', $exception->getMessage()));
        }

        $schoolYear = new SchoolYear();
        $start = $schoolYear->currentStart(
            new \DateTimeImmutable('now'),
            new \DateTimeImmutable($settings->schoolYearStart),
            new \DateTimeImmutable($settings->schoolYearEnd),
        );

        return [
            'inside' => $start->modify('+1 day')->setTime(18, 0),
            'before' => $start->modify('-1 day')->setTime(18, 0),
        ];
    }

    private function persistEvent(
        string $slug,
        EventType $type,
        EventVisibility $visibility,
        \DateTimeImmutable $startsAt,
        EventState $state = EventState::Open,
    ): Event {
        $event = (new Event())
            ->setTitle('Assemblée ICS')
            ->setSlug($slug)
            ->setDescription('Réunion des parents.')
            ->setStartsAt($startsAt)
            ->setType($type)
            ->setState($state)
            ->setVisibility($visibility)
            ->setIsAllDay(false);

        try {
            $this->entityManager->persist($event);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            self::markTestSkipped(sprintf('Database is not reachable for integration tests: %s', $exception->getMessage()));
        }

        $id = $event->getId();
        self::assertNotNull($id);
        $this->createdIds[] = $id;

        return $event;
    }

    private function paris(string $datetime): \DateTimeImmutable
    {
        return new \DateTimeImmutable($datetime, new \DateTimeZone(AppTimezone::NAME));
    }

    private function suffix(): string
    {
        return str_replace('.', '', uniqid('', true));
    }
}
