<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ContentTheme;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class ContentThemeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContentTheme::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Thème')
            ->setEntityLabelInPlural('Thèmes')
            ->setPageTitle(Crud::PAGE_INDEX, 'Thèmes')
            ->setDefaultSort(['weight' => 'DESC', 'name' => 'ASC']);
    }

    public function createEntity(string $entityFqcn): ContentTheme
    {
        return (new ContentTheme())->setWeight(0);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom')
            ->setHelp('Ex: Pastorale, APEL, Sport');
        yield TextField::new('icon', 'Icône')
            ->setHelp('Nom d’icône Material Design Icons, ex: mdi-school. Catalogue : https://pictogrammers.com/library/mdi/');
        yield IntegerField::new('weight', 'Ordre')
            ->setHelp('Plus le nombre est élevé, plus le thème apparaît en haut.');
    }
}
