<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Faq;
use App\Entity\Grade;
use App\Entity\Post;
use App\Entity\SchoolClass;
use App\Entity\SiteSettings;
use App\Entity\User;
use App\Service\SiteSettingsProvider;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly SiteSettingsProvider $settingsProvider,
    ) {
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        $settings = $this->settingsProvider->get();

        $dashboard = Dashboard::new()
            ->setTitle(htmlspecialchars($settings->siteName, ENT_QUOTES) . ' Admin');

        if (null !== $settings->faviconUrl) {
            $dashboard->setFaviconPath($settings->faviconUrl);
        }

        return $dashboard;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Content');
        yield MenuItem::linkToCrud('Events', 'fa fa-calendar', Event::class);
        yield MenuItem::linkToCrud('Posts', 'fa fa-newspaper', Post::class);
        yield MenuItem::linkToCrud('FAQs', 'fa fa-question-circle', Faq::class);
        yield MenuItem::section('École');
        yield MenuItem::linkToCrud('Classes', 'fa fa-chalkboard', SchoolClass::class);
        yield MenuItem::linkToCrud('Niveaux', 'fa fa-layer-group', Grade::class);
        yield MenuItem::section('Administration');
        yield MenuItem::linkToCrud('Comptes', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Réglages du site', 'fa fa-sliders', SiteSettings::class)
            ->setAction(Action::EDIT)
            ->setEntityId($this->settingsProvider->getEntity()->getId());
        yield MenuItem::linkToLogout('Logout', 'fa fa-sign-out');
    }
}
