<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Enum\ContentTheme;
use App\Enum\PostState;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Post')
            ->setEntityLabelInPlural('Posts')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextareaField::new('content', 'Contenu')->hideOnIndex();
        yield ChoiceField::new('theme', 'Thème')
            ->setChoices(
                array_combine(
                    array_map(fn (ContentTheme $theme) => $theme->label(), ContentTheme::cases()),
                    ContentTheme::cases(),
                )
            );
        yield AssociationField::new('author', 'Auteur');
        yield ChoiceField::new('state', 'État')
            ->setChoices(
                array_combine(
                    array_map(fn (PostState $state) => $state->label(), PostState::cases()),
                    PostState::cases(),
                )
            );
        yield AssociationField::new('grades', 'Niveaux')
            ->setHelp('Laisser vide pour toute l’école. Ne pas combiner avec des classes.')
            ->setFormTypeOption('by_reference', false)
            ->autocomplete();
        yield AssociationField::new('schoolClasses', 'Classes')
            ->setHelp('Laisser vide pour toute l’école. Ne pas combiner avec des niveaux.')
            ->setFormTypeOption('by_reference', false)
            ->autocomplete();
        yield IntegerField::new('viewCount')->hideOnForm();
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
    }

    public function createEntity(string $entityFqcn): Post
    {
        return (new Post())
            ->setViewCount(0)
            ->setState(PostState::Draft)
            ->setTheme(ContentTheme::Autre);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Post) {
            parent::persistEntity($entityManager, $entityInstance);

            return;
        }

        $now = new \DateTimeImmutable();
        $entityInstance->setCreatedAt($entityInstance->getCreatedAt() ?? $now);
        $entityInstance->setUpdatedAt($now);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Post) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        $entityInstance->setUpdatedAt(new \DateTimeImmutable());

        parent::updateEntity($entityManager, $entityInstance);
    }
}
