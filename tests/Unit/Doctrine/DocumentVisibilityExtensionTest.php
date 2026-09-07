<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Doctrine\DocumentVisibilityExtension;
use App\Entity\Document;
use App\Entity\Faq;
use App\Enum\DocumentVisibility;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class DocumentVisibilityExtensionTest extends TestCase
{
    public function testCollectionRestrictsDocumentToVisible(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['d']);
        $queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('d.visibility = :visibility')
            ->willReturnSelf();
        $queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('visibility', DocumentVisibility::Visible)
            ->willReturnSelf();

        $extension = new DocumentVisibilityExtension();
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Document::class,
        );
    }

    public function testCollectionIgnoresOtherResources(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::never())->method('getRootAliases');
        $queryBuilder->expects(self::never())->method('andWhere');

        $extension = new DocumentVisibilityExtension();
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Faq::class,
        );
    }

    public function testItemRestrictsDocumentToVisible(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['d']);
        $queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('d.visibility = :visibility')
            ->willReturnSelf();
        $queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('visibility', DocumentVisibility::Visible)
            ->willReturnSelf();

        $extension = new DocumentVisibilityExtension();
        $extension->applyToItem(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Document::class,
            ['id' => 1],
        );
    }
}
