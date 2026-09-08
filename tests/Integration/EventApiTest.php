<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Event;
use App\Enum\EventState;
use App\Enum\EventType;
use App\Enum\EventVisibility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventApiTest extends WebTestCase
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

    public function testVisibleEventItemIsReadable(): void
    {
        $event = $this->persistEvent(EventVisibility::Visible);

        $this->client->request('GET', '/api/events/'.$event->getId(), server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($event->getTitle(), $payload['title']);
        self::assertFalse($payload['isAllDay'] ?? false);
    }

    public function testGreyedOutEventItemIsNotFound(): void
    {
        $event = $this->persistEvent(EventVisibility::GreyedOut);

        $this->client->request('GET', '/api/events/'.$event->getId(), server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testGreyedOutEventStillAppearsInCollection(): void
    {
        $event = $this->persistEvent(EventVisibility::GreyedOut);

        $this->client->request('GET', '/api/events', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);
        $ids = array_map(static fn (array $row): int => $row['id'], $payload);
        self::assertContains($event->getId(), $ids);
    }

    private function persistEvent(EventVisibility $visibility): Event
    {
        $event = (new Event())
            ->setTitle('Assemblée '.$visibility->value.' '.uniqid('', true))
            ->setDescription('Réunion des parents.')
            ->setStartsAt(new \DateTimeImmutable('2026-09-12 18:00:00'))
            ->setType(EventType::PublicMeeting)
            ->setState(EventState::Open)
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
}
