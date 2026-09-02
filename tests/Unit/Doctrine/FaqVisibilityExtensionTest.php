<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Doctrine\FaqVisibilityExtension;
use App\Entity\Event;
use App\Entity\Faq;
use App\Enum\FaqVisibility;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class FaqVisibilityExtensionTest extends TestCase
{
    public function testCollectionRestrictsFaqToVisible(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['f']);
        $queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('f.visibility = :visibility')
            ->willReturnSelf();
        $queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('visibility', FaqVisibility::Visible)
            ->willReturnSelf();

        $extension = new FaqVisibilityExtension();
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Faq::class,
        );
    }

    public function testCollectionIgnoresOtherResources(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::never())->method('getRootAliases');
        $queryBuilder->expects(self::never())->method('andWhere');

        $extension = new FaqVisibilityExtension();
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Event::class,
        );
    }

    public function testItemRestrictsFaqToVisible(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['f']);
        $queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('f.visibility = :visibility')
            ->willReturnSelf();
        $queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('visibility', FaqVisibility::Visible)
            ->willReturnSelf();

        $extension = new FaqVisibilityExtension();
        $extension->applyToItem(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Faq::class,
            ['id' => 1],
        );
    }
}
