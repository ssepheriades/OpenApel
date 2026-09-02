<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Doctrine\EventVisibilityExtension;
use App\Entity\Event;
use App\Entity\Faq;
use App\Enum\EventVisibility;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class EventVisibilityExtensionTest extends TestCase
{
    public function testCollectionRestrictsEventToPublicVisibilities(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['e']);
        $queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('e.visibility IN (:visibilities)')
            ->willReturnSelf();
        $queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('visibilities', [EventVisibility::Visible, EventVisibility::GreyedOut])
            ->willReturnSelf();

        $extension = new EventVisibilityExtension();
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Event::class,
        );
    }

    public function testCollectionIgnoresOtherResources(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::never())->method('getRootAliases');
        $queryBuilder->expects(self::never())->method('andWhere');

        $extension = new EventVisibilityExtension();
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Faq::class,
        );
    }

    public function testItemRestrictsEventToPublicVisibilities(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['e']);
        $queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('e.visibility IN (:visibilities)')
            ->willReturnSelf();
        $queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('visibilities', [EventVisibility::Visible, EventVisibility::GreyedOut])
            ->willReturnSelf();

        $extension = new EventVisibilityExtension();
        $extension->applyToItem(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Event::class,
            ['id' => 1],
        );
    }
}
