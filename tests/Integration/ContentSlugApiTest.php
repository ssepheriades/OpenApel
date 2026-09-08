<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\ContentTheme;
use App\Entity\Event;
use App\Entity\Grade;
use App\Entity\Post;
use App\Entity\SchoolClass;
use App\Entity\SiteSettings;
use App\Entity\User;
use App\Enum\EventState;
use App\Enum\EventType;
use App\Enum\EventVisibility;
use App\Enum\PostState;
use App\Enum\UserRole;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContentSlugApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        static::bootKernel();
        $this->client = new KernelBrowser(static::$kernel);

        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $metadata = [
            $this->entityManager->getClassMetadata(User::class),
            $this->entityManager->getClassMetadata(SiteSettings::class),
            $this->entityManager->getClassMetadata(ContentTheme::class),
            $this->entityManager->getClassMetadata(Grade::class),
            $this->entityManager->getClassMetadata(SchoolClass::class),
            $this->entityManager->getClassMetadata(Post::class),
            $this->entityManager->getClassMetadata(Event::class),
        ];
        $schemaTool = new SchemaTool($this->entityManager);

        try {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        } catch (DbalException $exception) {
            self::markTestSkipped(sprintf('Database is not reachable for integration tests: %s', $exception->getMessage()));
        }
    }

    public function testPublishedPostIsFetchedBySlug(): void
    {
        $post = $this->persistPost('Assemblée générale', PostState::Published);

        $this->client->request('GET', '/api/posts/'.$post->getSlug(), server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('assemblee-generale-2026', $payload['slug']);
        self::assertSame('Assemblée générale', $payload['title']);
        self::assertArrayHasKey('id', $payload);
    }

    public function testDraftPostIsNotFoundBySlug(): void
    {
        $post = $this->persistPost('Brouillon secret', PostState::Draft);

        $this->client->request('GET', '/api/posts/'.$post->getSlug(), server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testNumericPostIdIsNotFound(): void
    {
        $post = $this->persistPost('Actualité', PostState::Published);
        self::assertNotNull($post->getId());

        $this->client->request('GET', '/api/posts/'.$post->getId(), server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testVisibleEventIsFetchedBySlug(): void
    {
        $event = $this->persistEvent('Kermesse', EventVisibility::Visible);

        $this->client->request('GET', '/api/events/'.$event->getSlug(), server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('kermesse-2026', $payload['slug']);
    }

    public function testGreyedOutEventIsNotFoundBySlug(): void
    {
        $event = $this->persistEvent('Conseil', EventVisibility::GreyedOut);

        $this->client->request('GET', '/api/events/'.$event->getSlug(), server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testDuplicateTitlesGetDistinctSlugs(): void
    {
        $first = $this->persistPost('Kermesse', PostState::Published);
        $second = $this->persistPost('Kermesse', PostState::Published);

        self::assertSame('kermesse-2026', $first->getSlug());
        self::assertSame('kermesse-2026-2', $second->getSlug());
    }

    public function testDuplicateExplicitSlugIsRejected(): void
    {
        $this->persistPost('Première', PostState::Published, 'meme-adresse');

        $this->expectException(UniqueConstraintViolationException::class);
        $this->persistPost('Deuxième', PostState::Published, 'meme-adresse');
    }

    private function persistPost(string $title, PostState $state, ?string $slug = null): Post
    {
        $author = (new User())
            ->setEmail(sprintf('author-%s@example.com', bin2hex(random_bytes(4))))
            ->setFirstName('Auteur')
            ->setLastName('Test')
            ->setRoles([UserRole::Admin->value])
            ->setIsActive(true);
        $author->setPassword(password_hash('unused', PASSWORD_BCRYPT));

        $theme = (new ContentTheme())
            ->setName('Thème '.bin2hex(random_bytes(3)))
            ->setIcon('mdi-newspaper')
            ->setWeight(10);

        $now = new \DateTimeImmutable('2026-09-08 12:00:00');
        $post = (new Post())
            ->setTitle($title)
            ->setContent('Contenu')
            ->setAuthor($author)
            ->setTheme($theme)
            ->setState($state)
            ->setCreatedAt($now)
            ->setUpdatedAt($now)
            ->setViewCount(0)
            ->setSlug($slug);

        $this->entityManager->persist($author);
        $this->entityManager->persist($theme);
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }

    private function persistEvent(string $title, EventVisibility $visibility): Event
    {
        $event = (new Event())
            ->setTitle($title)
            ->setDescription('Description')
            ->setStartsAt(new \DateTimeImmutable('2026-06-14 10:00:00'))
            ->setType(EventType::SchoolEvent)
            ->setState(EventState::Open)
            ->setVisibility($visibility);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }
}
