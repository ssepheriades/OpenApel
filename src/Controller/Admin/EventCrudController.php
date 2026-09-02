<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Enum\EventState;
use App\Enum\EventType;
use App\Enum\EventVisibility;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Vich\UploaderBundle\Form\Type\VichImageType;

final class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Event')
            ->setEntityLabelInPlural('Events')
            ->setDefaultSort(['startsAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title');
        yield TextareaField::new('description')->hideOnIndex();
        yield TextField::new('shortDescription');
        yield DateTimeField::new('startsAt');
        yield DateTimeField::new('endsAt');
        yield TextField::new('location');
        yield UrlField::new('ticketingUrl')->hideOnIndex();
        yield ChoiceField::new('type')
            ->setChoices(
                array_combine(
                    array_map(fn (EventType $t) => $t->label(), EventType::cases()),
                    EventType::cases()
                )
            );
        yield ChoiceField::new('state')
            ->setChoices(
                array_combine(
                    array_map(fn (EventState $s) => $s->label(), EventState::cases()),
                    EventState::cases()
                )
            );
        yield ChoiceField::new('visibility')
            ->setChoices(
                array_combine(
                    array_map(fn (EventVisibility $v) => $v->label(), EventVisibility::cases()),
                    EventVisibility::cases()
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
        yield ImageField::new('heroImageFilename', 'Hero')
            ->setBasePath('/uploads/photos')
            ->onlyOnIndex();
        yield Field::new('heroImageFile', 'Hero')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => false])
            ->onlyOnForms();
        yield ImageField::new('flyerImageFilename', 'Flyer')
            ->setBasePath('/uploads/photos')
            ->onlyOnIndex();
        yield Field::new('flyerImageFile', 'Flyer')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => false])
            ->onlyOnForms();
    }
}
