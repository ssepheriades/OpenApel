<?php

declare(strict_types=1);

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event;
use App\Enum\EventVisibility;
use Doctrine\ORM\QueryBuilder;

final class EventVisibilityExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->restrictToVisibilities(
            $queryBuilder,
            $resourceClass,
            [EventVisibility::Visible, EventVisibility::GreyedOut],
        );
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->restrictToVisibilities(
            $queryBuilder,
            $resourceClass,
            [EventVisibility::Visible],
        );
    }

    /**
     * @param list<EventVisibility> $visibilities
     */
    private function restrictToVisibilities(QueryBuilder $queryBuilder, string $resourceClass, array $visibilities): void
    {
        if (Event::class !== $resourceClass) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.visibility IN (:visibilities)', $alias))
            ->setParameter('visibilities', $visibilities);
    }
}
