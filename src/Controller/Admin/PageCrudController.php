<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Field\MarkdownEditorField;
use App\Entity\Page;
use App\Enum\PageKind;
use App\Enum\PageSlug;
use App\Repository\PageRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Catalogue CRUD: staff edit copy, they cannot create or delete slots.
 */
final class PageCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $this->pageRepository->ensureCatalog();

        return $crud
            ->setEntityLabelInSingular('Page')
            ->setEntityLabelInPlural('Pages')
            ->setPageTitle(Crud::PAGE_INDEX, 'Pages')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la page')
            ->setDefaultSort(['title' => 'ASC'])
            ->setSearchFields(['title']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        $page = $this->getContext()?->getEntity()->getInstance();
        $slug = $page instanceof Page ? $page->getSlug() : null;

        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextField::new('slugValue', 'Identifiant')->hideOnForm();
        yield TextField::new('kindLabel', 'Type')->hideOnForm();
        yield TextField::new('visibilityLabel', 'Visible')->onlyOnIndex();

        if (Crud::PAGE_INDEX === $pageName) {
            yield DateTimeField::new('updatedAt', 'Modifiée')->hideOnForm();

            return;
        }

        if (null === $slug || $slug->usesVisibility()) {
            $help = PageKind::Document === $slug?->kind()
                ? 'Afficher le lien dans le pied de page et rendre la page accessible.'
                : 'Afficher cette rubrique dans le menu et sur le site public.';

            yield BooleanField::new('visible', 'Visible')
                ->renderAsSwitch()
                ->setHelp($help);
        }

        if (null === $slug || $slug->usesSubtitle()) {
            yield TextField::new('subtitle', 'Chapô')
                ->setHelp('Sous-titre affiché sous le titre, en haut de la page.');
        }

        if (null === $slug || $slug->usesBody()) {
            $help = PageSlug::Home === $slug
                ? 'Texte d\'introduction sous le titre. Gras, listes et liens via la barre d\'outils.'
                : 'Contenu de la page. Gras, listes et liens via la barre d\'outils.';

            yield MarkdownEditorField::new('body', 'Contenu')->setHelp($help);
        }

        yield DateTimeField::new('updatedAt', 'Modifiée')->hideOnForm();
    }
}
