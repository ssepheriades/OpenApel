<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Page;
use App\Enum\PageSlug;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Not final on purpose: PageCatalogProvider unit tests double this repository.
 *
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * Inserts any catalogue slug missing from the table (tests, empty schemas).
     *
     * @return list<Page>
     */
    public function ensureCatalog(): array
    {
        /** @var array<string, Page> $bySlug */
        $bySlug = [];
        foreach ($this->findAll() as $page) {
            $bySlug[$page->getSlug()->value] = $page;
        }

        $entityManager = $this->getEntityManager();
        $dirty = false;

        foreach (PageSlug::cases() as $slug) {
            if (isset($bySlug[$slug->value])) {
                continue;
            }

            $page = Page::fromSlug($slug);
            $entityManager->persist($page);
            $bySlug[$slug->value] = $page;
            $dirty = true;
        }

        if ($dirty) {
            $entityManager->flush();
        }

        $ordered = [];
        foreach (PageSlug::cases() as $slug) {
            $ordered[] = $bySlug[$slug->value];
        }

        return $ordered;
    }
}
