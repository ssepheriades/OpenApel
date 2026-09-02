<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

final class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Compte')
            ->setEntityLabelInPlural('Comptes')
            ->setDefaultSort(['weight' => 'DESC', 'lastName' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email');
        yield TextField::new('firstName');
        yield TextField::new('lastName');
        yield TelephoneField::new('phone');
        yield TextField::new('position')->setHelp('Ex: Trésorier, Secrétaire, Trésorier Adjoint...');
        yield IntegerField::new('weight')->setHelp('Plus le nombre est élevé, plus le membre apparaît en haut de la page Équipe.');
        yield TextField::new('shortBio')->setMaxLength(80);
        yield TextareaField::new('bio');
        yield ChoiceField::new('roles')
            ->setChoices([
                'Administrateur (accès back-office)' => UserRole::Admin->value,
                "Membre de l'équipe (page publique)" => UserRole::Member->value,
            ])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->renderAsBadges();
        yield ImageField::new('photoFilename', 'Photo')
            ->setBasePath('/uploads/photos')
            ->onlyOnIndex();
        yield Field::new('photoFile', 'Photo')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => false])
            ->onlyOnForms();
        yield BooleanField::new('isActive');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
    }

    public function createEntity(string $entityFqcn): User
    {
        return (new User())
            ->setRoles([UserRole::Member->value])
            ->setIsActive(true);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance, isNew: true);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance, isNew: false);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPassword(User $user, bool $isNew = false): void
    {
        $plainPassword = $user->getPlainPassword();

        if (null === $plainPassword) {
            if ($isNew) {
                $plainPassword = bin2hex(random_bytes(32));
            } else {
                return;
            }
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->eraseCredentials();
    }
}
