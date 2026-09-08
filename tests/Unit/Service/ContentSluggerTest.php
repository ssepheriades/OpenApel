<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Event;
use App\Entity\Post;
use App\Repository\EventRepository;
use App\Repository\PostRepository;
use App\Service\ContentSlugger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ContentSluggerTest extends TestCase
{
    private PostRepository&MockObject $posts;
    private EventRepository&MockObject $events;
    private EntityManagerInterface&MockObject $entityManager;
    private UnitOfWork&MockObject $unitOfWork;
    private ContentSlugger $slugger;

    /** @var list<object> */
    private array $scheduledInsertions = [];

    protected function setUp(): void
    {
        $this->posts = $this->createMock(PostRepository::class);
        $this->events = $this->createMock(EventRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->unitOfWork = $this->createMock(UnitOfWork::class);
        $this->scheduledInsertions = [];

        $this->entityManager->method('getUnitOfWork')->willReturn($this->unitOfWork);
        $this->entityManager->method('getRepository')->willReturnCallback(
            fn (string $className): PostRepository|EventRepository => Event::class === $className
                ? $this->events
                : $this->posts,
        );
        $this->unitOfWork->method('getScheduledEntityInsertions')->willReturnCallback(
            fn (): array => $this->scheduledInsertions,
        );

        $this->slugger = new ContentSlugger();
    }

    public function testSlugifyTransliteratesFrench(): void
    {
        self::assertSame('assemblee-generale', $this->slugger->slugify('Assemblée générale', ContentSlugger::POST_FALLBACK));
        self::assertSame('noel', $this->slugger->slugify('Noël', ContentSlugger::POST_FALLBACK));
    }

    public function testSlugifyFallsBackWhenTitleIsUnusable(): void
    {
        self::assertSame('article', $this->slugger->slugify('!!!', ContentSlugger::POST_FALLBACK));
        self::assertSame('evenement', $this->slugger->slugify('?', ContentSlugger::EVENT_FALLBACK));
        self::assertSame('article', $this->slugger->slugify('A', ContentSlugger::POST_FALLBACK));
    }

    public function testSlugifyTruncatesToMaxLength(): void
    {
        $slug = $this->slugger->slugify(str_repeat('é', 100), ContentSlugger::POST_FALLBACK);

        self::assertSame(ContentSlugger::MAX_LENGTH, strlen($slug));
        self::assertSame(str_repeat('e', ContentSlugger::MAX_LENGTH), $slug);
    }

    public function testWithYearAppendsStartYearWhenMissing(): void
    {
        $startsAt = new \DateTimeImmutable('2026-06-14');

        self::assertSame('kermesse-2026', $this->slugger->withYear('kermesse', $startsAt));
    }

    public function testWithYearDoesNotDuplicateYearSegment(): void
    {
        $startsAt = new \DateTimeImmutable('2026-06-14');

        self::assertSame('kermesse-2026', $this->slugger->withYear('kermesse-2026', $startsAt));
        self::assertSame('2026', $this->slugger->withYear('2026', $startsAt));
    }

    public function testWithYearLeavesSlugAloneWithoutStartDate(): void
    {
        self::assertSame('kermesse', $this->slugger->withYear('kermesse', null));
    }

    public function testAssignDoesNotOverwriteAnExistingSlug(): void
    {
        $this->posts->expects(self::never())->method('existsSlug');

        $post = (new Post())->setTitle('Nouveau titre')->setSlug('slug-historique');
        $this->slugger->assign($post, $this->entityManager);

        self::assertSame('slug-historique', $post->getSlug());
    }

    public function testAssignBuildsPostSlugFromTitleAndYear(): void
    {
        $this->posts->method('existsSlug')->willReturn(false);

        $post = (new Post())
            ->setTitle('Assemblée générale')
            ->setCreatedAt(new \DateTimeImmutable('2026-03-01'));
        $this->slugger->assign($post, $this->entityManager);

        self::assertSame('assemblee-generale-2026', $post->getSlug());
    }

    public function testAssignDoesNotDuplicateYearAlreadyInPostTitle(): void
    {
        $this->posts->method('existsSlug')->willReturn(false);

        $post = (new Post())
            ->setTitle('Noël 2026')
            ->setCreatedAt(new \DateTimeImmutable('2026-12-10'));
        $this->slugger->assign($post, $this->entityManager);

        self::assertSame('noel-2026', $post->getSlug());
    }

    public function testAssignAppendsYearForEvents(): void
    {
        $this->events->method('existsSlug')->willReturn(false);

        $event = (new Event())
            ->setTitle('Kermesse')
            ->setStartsAt(new \DateTimeImmutable('2026-06-14 10:00:00'));
        $this->slugger->assign($event, $this->entityManager);

        self::assertSame('kermesse-2026', $event->getSlug());
    }

    public function testAssignSuffixesOnCollisionIncludingInMemory(): void
    {
        $this->posts->method('existsSlug')->willReturn(false);

        $first = (new Post())
            ->setTitle('Kermesse')
            ->setCreatedAt(new \DateTimeImmutable('2026-06-14'));
        $this->slugger->assign($first, $this->entityManager);
        $this->scheduledInsertions = [$first];

        $second = (new Post())
            ->setTitle('Kermesse')
            ->setCreatedAt(new \DateTimeImmutable('2026-06-20'));
        $this->slugger->assign($second, $this->entityManager);

        self::assertSame('kermesse-2026', $first->getSlug());
        self::assertSame('kermesse-2026-2', $second->getSlug());
    }

    public function testAssignKeepsSameTitleDistinctAcrossYears(): void
    {
        $this->posts->method('existsSlug')->willReturn(false);

        $first = (new Post())
            ->setTitle('Kermesse')
            ->setCreatedAt(new \DateTimeImmutable('2026-06-14'));
        $this->slugger->assign($first, $this->entityManager);
        $this->scheduledInsertions = [$first];

        $second = (new Post())
            ->setTitle('Kermesse')
            ->setCreatedAt(new \DateTimeImmutable('2027-06-14'));
        $this->slugger->assign($second, $this->entityManager);

        self::assertSame('kermesse-2026', $first->getSlug());
        self::assertSame('kermesse-2027', $second->getSlug());
    }

    public function testAssignSuffixesWhenDatabaseAlreadyHasSlug(): void
    {
        $this->posts->method('existsSlug')->willReturnCallback(
            static fn (string $slug): bool => 'assemblee-generale-2026' === $slug,
        );

        $post = (new Post())
            ->setTitle('Assemblée générale')
            ->setCreatedAt(new \DateTimeImmutable('2026-03-01'));
        $this->slugger->assign($post, $this->entityManager);

        self::assertSame('assemblee-generale-2026-2', $post->getSlug());
    }
}
