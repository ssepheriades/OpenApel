<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Faq;
use App\Enum\FaqVisibility;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class FaqCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Faq::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('FAQ')
            ->setEntityLabelInPlural('FAQs')
            ->setPageTitle(Crud::PAGE_INDEX, 'FAQs')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function createEntity(string $entityFqcn): Faq
    {
        return (new Faq())
            ->setVisibility(FaqVisibility::Visible);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('question', 'Question');
        yield TextareaField::new('answer', 'Réponse')->hideOnIndex();
        yield AssociationField::new('theme', 'Thème')
            ->autocomplete();
        yield ChoiceField::new('visibility', 'Visibilité')
            ->setChoices(
                array_combine(
                    array_map(fn (FaqVisibility $visibility) => $visibility->label(), FaqVisibility::cases()),
                    FaqVisibility::cases(),
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
        yield DateTimeField::new('createdAt', 'Date de saisie')->hideOnForm();
    }
}
