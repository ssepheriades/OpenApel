<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SchoolClass;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class SchoolClassCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SchoolClass::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Classe')
            ->setEntityLabelInPlural('Classes')
            ->setPageTitle(Crud::PAGE_INDEX, 'Classes')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom')
            ->setHelp('Ex: CE1-A');
        yield AssociationField::new('grade', 'Niveau');
        yield TextField::new('teacher', 'Enseignant(e)');
    }
}
