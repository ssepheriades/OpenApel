<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Enum\DocumentVisibility;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichFileType;

final class DocumentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Document')
            ->setEntityLabelInPlural('Documents')
            ->setPageTitle(Crud::PAGE_INDEX, 'Documents')
            ->setDefaultSort(['date' => 'DESC']);
    }

    public function createEntity(string $entityFqcn): Document
    {
        return (new Document())
            ->setVisibility(DocumentVisibility::Visible);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextareaField::new('description', 'Description')
            ->hideOnIndex();
        yield AssociationField::new('theme', 'Thème')
            ->autocomplete();
        yield ChoiceField::new('visibility', 'Visibilité')
            ->setChoices(
                array_combine(
                    array_map(fn (DocumentVisibility $visibility) => $visibility->label(), DocumentVisibility::cases()),
                    DocumentVisibility::cases(),
                )
            );
        yield DateTimeField::new('date', 'Date');
        yield TextField::new('filename', 'Fichier')
            ->onlyOnIndex();
        yield Field::new('file', 'Fichier')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions(['allow_delete' => false, 'download_uri' => true])
            ->onlyOnForms();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Document) {
            parent::persistEntity($entityManager, $entityInstance);

            return;
        }

        $entityInstance->setUpdatedAt(new \DateTimeImmutable());

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Document) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        $entityInstance->setUpdatedAt(new \DateTimeImmutable());

        parent::updateEntity($entityManager, $entityInstance);
    }
}
