<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Event;
use App\Entity\Post;
use App\Service\ContentSlugger;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class ContentSlugListener
{
    public function __construct(
        private readonly ContentSlugger $contentSlugger,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        if ((!$entity instanceof Post && !$entity instanceof Event) || !$entityManager instanceof EntityManagerInterface) {
            return;
        }

        $this->contentSlugger->assign($entity, $entityManager);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        if ((!$entity instanceof Post && !$entity instanceof Event) || !$entityManager instanceof EntityManagerInterface) {
            return;
        }

        $this->contentSlugger->assign($entity, $entityManager);
        $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $entityManager->getClassMetadata($entity::class),
            $entity,
        );
    }
}
