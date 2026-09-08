<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event;
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
