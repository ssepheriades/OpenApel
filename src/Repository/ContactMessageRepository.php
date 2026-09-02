<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ContactMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactMessage>
 */
class ContactMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactMessage::class);
    }

    public function countUnprocessed(): int
    {
        return (int) $this->createQueryBuilder('contactMessage')
            ->select('COUNT(contactMessage.id)')
            ->andWhere('contactMessage.processed = false')
            ->andWhere('contactMessage.archived = false')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
