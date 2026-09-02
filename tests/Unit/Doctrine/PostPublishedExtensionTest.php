<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Doctrine\PostPublishedExtension;
use App\Entity\Faq;
use App\Entity\Post;
use App\Enum\PostState;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class PostPublishedExtensionTest extends TestCase
{
    public function testCollectionRestrictsPostToPublished(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['p']);
        $queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('p.state = :state')
            ->willReturnSelf();
        $queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('state', PostState::Published)
            ->willReturnSelf();

        $extension = new PostPublishedExtension();
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Post::class,
        );
    }

    public function testCollectionIgnoresOtherResources(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::never())->method('getRootAliases');
        $queryBuilder->expects(self::never())->method('andWhere');

        $extension = new PostPublishedExtension();
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Faq::class,
        );
    }

    public function testItemRestrictsPostToPublished(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['p']);
        $queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('p.state = :state')
            ->willReturnSelf();
        $queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('state', PostState::Published)
            ->willReturnSelf();

        $extension = new PostPublishedExtension();
        $extension->applyToItem(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Post::class,
            ['id' => 1],
        );
    }
}
