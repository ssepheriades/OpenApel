<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SiteSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Not final on purpose: SiteSettingsProvider unit tests double this repository.
 *
 * @extends ServiceEntityRepository<SiteSettings>
 */
class SiteSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteSettings::class);
    }

    /**
     * The row is normally seeded by the migration; the fallback covers
     * test schemas built with SchemaTool and manually emptied tables.
     */
    public function getOrCreate(): SiteSettings
    {
        $settings = $this->findOneBy([], ['id' => 'ASC']);

        if (null !== $settings) {
            return $settings;
        }

        $settings = new SiteSettings();
        $entityManager = $this->getEntityManager();
        $entityManager->persist($settings);
        $entityManager->flush();

        return $settings;
    }
}
