<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Event;
use App\Entity\Post;
use App\Repository\EventRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class ContentSlugger
{
    public const int MAX_LENGTH = 80;
    public const int MIN_LENGTH = 2;
    public const string PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    public const string POST_FALLBACK = 'article';
    public const string EVENT_FALLBACK = 'evenement';

    private readonly AsciiSlugger $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger('fr');
    }

    public function assign(Post|Event $entity, EntityManagerInterface $entityManager): void
    {
        if (null !== $entity->getSlug()) {
            return;
        }

        $fallback = $entity instanceof Event ? self::EVENT_FALLBACK : self::POST_FALLBACK;
        $date = $entity instanceof Event
            ? $entity->getStartsAt()
            : $entity->getCreatedAt();
        $base = $this->withYear(
            $this->slugify($entity->getTitle() ?? '', $fallback),
            $date ?? new \DateTimeImmutable(),
        );

        $entity->setSlug($this->uniquify($base, $entity, $entityManager));
    }

    public function slugify(string $title, string $fallback): string
    {
        $slug = strtolower($this->slugger->slug($title)->toString());
        if (strlen($slug) < self::MIN_LENGTH) {
            $slug = $fallback;
        }

        return $this->truncate($slug, self::MAX_LENGTH);
    }

    public function withYear(string $slug, ?\DateTimeImmutable $date): string
    {
        if (null === $date) {
            return $slug;
        }

        $year = $date->format('Y');
        if ($this->containsYearSegment($slug, $year)) {
            return $slug;
        }

        $suffix = '-'.$year;

        return $this->truncate($slug, self::MAX_LENGTH - strlen($suffix)).$suffix;
    }

    private function uniquify(string $base, Post|Event $entity, EntityManagerInterface $entityManager): string
    {
        if (!$this->isTaken($entity, $base, $entityManager)) {
            return $base;
        }

        for ($n = 2; $n <= 1000; ++$n) {
            $suffix = '-'.$n;
            $candidate = $this->truncate($base, self::MAX_LENGTH - strlen($suffix)).$suffix;
            if (!$this->isTaken($entity, $candidate, $entityManager)) {
                return $candidate;
            }
        }

        throw new \LogicException(sprintf('Unable to allocate a unique slug from "%s".', $base));
    }

    private function isTaken(Post|Event $entity, string $slug, EntityManagerInterface $entityManager): bool
    {
        foreach ($entityManager->getUnitOfWork()->getScheduledEntityInsertions() as $scheduled) {
            if ($scheduled === $entity) {
                continue;
            }

            if ($entity instanceof Event && $scheduled instanceof Event && $scheduled->getSlug() === $slug) {
                return true;
            }

            if ($entity instanceof Post && $scheduled instanceof Post && $scheduled->getSlug() === $slug) {
                return true;
            }
        }

        $repository = $entityManager->getRepository($entity::class);
        if ($repository instanceof EventRepository) {
            return $repository->existsSlug($slug, $entity->getId());
        }

        if ($repository instanceof PostRepository) {
            return $repository->existsSlug($slug, $entity->getId());
        }

        throw new \LogicException(sprintf('Unexpected repository for %s.', $entity::class));
    }

    private function containsYearSegment(string $slug, string $year): bool
    {
        return \in_array($year, explode('-', $slug), true);
    }

    private function truncate(string $slug, int $max): string
    {
        if (strlen($slug) <= $max) {
            return $slug;
        }

        return rtrim(substr($slug, 0, $max), '-');
    }
}
