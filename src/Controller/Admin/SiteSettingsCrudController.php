<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SiteSettings;
use App\Service\SiteSettingsProvider;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Singleton CRUD: only the edit page of the unique row is reachable.
 */
final class SiteSettingsCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SiteSettingsProvider $settingsProvider,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return SiteSettings::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Réglages du site')
            ->setEntityLabelInPlural('Réglages du site')
            ->setPageTitle(Crud::PAGE_EDIT, 'Réglages du site');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::DETAIL, Action::INDEX)
            // Stay on the form after saving: the index page does not exist for a singleton.
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, static fn (Action $action): Action => $action->setLabel('Enregistrer'));
    }

    /**
     * Safety net for a direct hit on the (disabled) index action.
     */
    public function index(AdminContext $context): Response
    {
        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::EDIT)
                ->setEntityId($this->settingsProvider->getEntity()->getId())
                ->generateUrl(),
        );
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Identité');
        yield TextField::new('siteName', 'Nom du site');
        yield TextField::new('baseline', 'Baseline')->setHelp('Phrase d\'accroche affichée sous le nom du site.');

        yield FormField::addFieldset('Page d\'accueil');
        yield TextField::new('homeTitle', 'Titre')
            ->setHelp('Titre affiché en grand sur la page d\'accueil. Si vide, le nom du site est utilisé.');
        yield TextareaField::new('homeText', 'Texte d\'introduction')
            ->setNumOfRows(10)
            ->setHelp('Markdown accepté (gras, listes, liens…). Laissé vide, seul le titre est affiché.');

        yield FormField::addFieldset('Images');
        yield Field::new('logoFile', 'Logo')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false])
            ->setHelp('JPEG, PNG, WebP ou SVG, 2 Mo max.');
        yield Field::new('faviconFile', 'Favicon')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false])
            ->setHelp('PNG ou ICO, 512 Ko max.');

        yield FormField::addFieldset('Contact & réseaux');
        yield EmailField::new('contactEmail', 'Email de contact');
        yield UrlField::new('facebookUrl', 'Page Facebook');
        yield UrlField::new('instagramUrl', 'Compte Instagram');

        yield FormField::addFieldset('Couleurs');
        yield ColorField::new('primaryColor', 'Couleur principale');
        yield ColorField::new('secondaryColor', 'Couleur secondaire');

        yield FormField::addFieldset('Année scolaire');
        yield DateField::new('schoolYearStart', 'Début')
            ->setHelp('Seuls le jour et le mois sont utilisés. L\'année en cours est calculée automatiquement.');
        yield DateField::new('schoolYearEnd', 'Fin')
            ->setHelp('Seuls le jour et le mois sont utilisés. L\'année en cours est calculée automatiquement.');

        yield FormField::addFieldset('Pages publiques');
        yield BooleanField::new('faqVisible', 'FAQ')
            ->setHelp('Afficher la page FAQ dans le menu et sur le site public.');
        yield BooleanField::new('teamVisible', 'Équipe')
            ->setHelp('Afficher la page Équipe dans le menu et sur le site public.');
        yield BooleanField::new('postsVisible', 'Actualités')
            ->setHelp('Afficher les actualités dans le menu et sur le site public.');
        yield BooleanField::new('agendaVisible', 'Agenda')
            ->setHelp('Afficher l\'agenda dans le menu et sur le site public.');
    }
}
