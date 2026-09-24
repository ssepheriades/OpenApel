<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event;
use App\Enum\EventType;
use App\Enum\EventVisibility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[]
     */
    public function findByImageFilename(string $filename): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.heroImageFilename = :filename OR e.flyerImageFilename = :filename')
            ->setParameter('filename', $filename)
            ->getQuery()
            ->getResult();
    }

    /**
     * Public iCal feed: visible, exportable types, from the current school-year start onward.
     *
     * @return list<Event>
     */
    public function findForIcsFeed(\DateTimeImmutable $startsAtFrom): array
    {
        /** @var list<Event> $events */
        $events = $this->createQueryBuilder('e')
            ->andWhere('e.visibility = :visibility')
            ->andWhere('e.type IN (:types)')
            ->andWhere('e.startsAt >= :startsAtFrom')
            ->setParameter('visibility', EventVisibility::Visible)
            ->setParameter('types', EventType::calendarExportableCases())
            ->setParameter('startsAtFrom', $startsAtFrom)
            ->orderBy('e.startsAt', 'ASC')
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $events;
    }

    public function findVisibleExportableBySlug(string $slug): ?Event
    {
        /** @var Event|null $event */
        $event = $this->createQueryBuilder('e')
            ->andWhere('e.slug = :slug')
            ->andWhere('e.visibility = :visibility')
            ->andWhere('e.type IN (:types)')
            ->setParameter('slug', $slug)
            ->setParameter('visibility', EventVisibility::Visible)
            ->setParameter('types', EventType::calendarExportableCases())
            ->getQuery()
            ->getOneOrNullResult();

        return $event;
    }

    public function existsSlug(string $slug, ?int $excludeId = null): bool
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.slug = :slug')
            ->setParameter('slug', $slug);

        if (null !== $excludeId) {
            $queryBuilder
                ->andWhere('e.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }
}
