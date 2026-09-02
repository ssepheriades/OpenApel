<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

final class ContactMessageCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ContactMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Message')
            ->setEntityLabelInPlural('Messages')
            ->setPageTitle(Crud::PAGE_INDEX, 'Messages')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Message')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('processed', 'Traité'))
            ->add(BooleanFilter::new('archived', 'Archivé'))
            ->add(EntityFilter::new('schoolClass', 'Classe'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $markProcessed = Action::new('markProcessed', 'Marquer comme traité')
            ->linkToCrudAction('markProcessed')
            ->displayIf(static fn (mixed $message): bool => $message instanceof ContactMessage && !$message->isProcessed());

        $markUnprocessed = Action::new('markUnprocessed', 'Marquer comme non traité')
            ->linkToCrudAction('markUnprocessed')
            ->displayIf(static fn (mixed $message): bool => $message instanceof ContactMessage && $message->isProcessed());

        $archive = Action::new('archive', 'Archiver')
            ->linkToCrudAction('archive')
            ->displayIf(static fn (mixed $message): bool => $message instanceof ContactMessage && !$message->isArchived());

        $unarchive = Action::new('unarchive', 'Désarchiver')
            ->linkToCrudAction('unarchive')
            ->displayIf(static fn (mixed $message): bool => $message instanceof ContactMessage && $message->isArchived());

        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $markProcessed)
            ->add(Crud::PAGE_INDEX, $markUnprocessed)
            ->add(Crud::PAGE_INDEX, $archive)
            ->add(Crud::PAGE_INDEX, $unarchive)
            ->add(Crud::PAGE_DETAIL, $markProcessed)
            ->add(Crud::PAGE_DETAIL, $markUnprocessed)
            ->add(Crud::PAGE_DETAIL, $archive)
            ->add(Crud::PAGE_DETAIL, $unarchive);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Reçu le');
        yield TextField::new('name', 'Nom');
        yield EmailField::new('email', 'Email');
        yield TelephoneField::new('phone', 'Téléphone')->hideOnIndex();
        yield TextField::new('subject', 'Sujet');
        yield AssociationField::new('schoolClass', 'Classe');
        yield TextareaField::new('message', 'Message')->hideOnIndex();
        yield BooleanField::new('processed', 'Traité')->renderAsSwitch(false);
        yield BooleanField::new('archived', 'Archivé')->renderAsSwitch(false);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $appliedFilters = $searchDto->getAppliedFilters() ?? [];

        if (!\array_key_exists('archived', $appliedFilters)) {
            $alias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->andWhere(sprintf('%s.archived = :hideArchived', $alias))
                ->setParameter('hideArchived', false);
        }

        return $queryBuilder;
    }

    #[AdminRoute('/{entityId}/mark-processed')]
    public function markProcessed(AdminContext $context): Response
    {
        return $this->toggleFlag($context, processed: true);
    }

    #[AdminRoute('/{entityId}/mark-unprocessed')]
    public function markUnprocessed(AdminContext $context): Response
    {
        return $this->toggleFlag($context, processed: false);
    }

    #[AdminRoute('/{entityId}/archive')]
    public function archive(AdminContext $context): Response
    {
        return $this->toggleFlag($context, archived: true);
    }

    #[AdminRoute('/{entityId}/unarchive')]
    public function unarchive(AdminContext $context): Response
    {
        return $this->toggleFlag($context, archived: false);
    }

    private function toggleFlag(AdminContext $context, ?bool $processed = null, ?bool $archived = null): Response
    {
        $message = $context->getEntity()->getInstance();
        if (!$message instanceof ContactMessage) {
            throw $this->createNotFoundException();
        }

        if (null !== $processed) {
            $message->setProcessed($processed);
        }

        if (null !== $archived) {
            $message->setArchived($archived);
        }

        $this->entityManager->flush();

        $this->addFlash('success', match (true) {
            true === $processed => 'Message marqué comme traité.',
            false === $processed => 'Message marqué comme non traité.',
            true === $archived => 'Message archivé.',
            false === $archived => 'Message désarchivé.',
            default => 'Message mis à jour.',
        });

        return $this->redirectToReferrer($context);
    }

    private function redirectToReferrer(AdminContext $context): Response
    {
        $referrer = $context->getReferrer();
        if (null !== $referrer && '' !== $referrer) {
            return $this->redirect($referrer);
        }

        return $this->redirect(
            $this->adminUrlGenerator
                ->unsetAll()
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl(),
        );
    }
}
