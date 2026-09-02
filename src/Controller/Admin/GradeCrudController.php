<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Grade;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class GradeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Grade::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Niveau')
            ->setEntityLabelInPlural('Niveaux')
            ->setPageTitle(Crud::PAGE_INDEX, 'Niveaux')
            ->setDefaultSort(['weight' => 'DESC', 'name' => 'ASC']);
    }

    public function createEntity(string $entityFqcn): Grade
    {
        return (new Grade())->setWeight(0);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom')
            ->setHelp('Ex: CP, CE1, CM2');
        yield IntegerField::new('weight', 'Ordre')
            ->setHelp('Plus le nombre est élevé, plus le niveau apparaît en haut.');
    }
}
