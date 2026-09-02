<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Controller\Admin\ContactMessageCrudController;
use App\Controller\Admin\ContentThemeCrudController;
use App\Controller\Admin\GradeCrudController;
use App\Controller\Admin\SchoolClassCrudController;
use App\Entity\ContactMessage;
use App\Entity\ContentTheme;
use App\Entity\Grade;
use App\Entity\SchoolClass;
use App\Entity\SiteSettings;
use App\Entity\User;
use App\Enum\UserRole;
use App\Service\SiteSettingsProvider;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SchoolAdminCrudTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        static::bootKernel();
        $this->client = new KernelBrowser(static::$kernel);

        $container = static::$kernel->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        $metadata = [
            $this->entityManager->getClassMetadata(User::class),
            $this->entityManager->getClassMetadata(SiteSettings::class),
            $this->entityManager->getClassMetadata(Grade::class),
            $this->entityManager->getClassMetadata(SchoolClass::class),
            $this->entityManager->getClassMetadata(ContentTheme::class),
            $this->entityManager->getClassMetadata(ContactMessage::class),
        ];
        $schemaTool = new SchemaTool($this->entityManager);

        try {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        } catch (DbalException $exception) {
            self::markTestSkipped(sprintf('Database is not reachable for integration tests: %s', $exception->getMessage()));
        }

        static::getContainer()->get(SiteSettingsProvider::class)->invalidate();
        static::getContainer()->get(SiteSettingsProvider::class)->getEntity();
    }

    public function testDashboardMenuListsSchoolCruds(): void
    {
        $this->loginAsAdmin();

        $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'École');
        self::assertSelectorTextContains('body', 'Classes');
        self::assertSelectorTextContains('body', 'Niveaux');
        self::assertSelectorTextContains('body', 'Thèmes');
        self::assertSelectorTextContains('body', 'Messages');
    }

    public function testContactMessageCrudIndexIsReachableWithoutNewAction(): void
    {
        $this->loginAsAdmin();

        $this->client->request('GET', '/admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => ContactMessageCrudController::class,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Messages');

        $this->client->request('GET', '/admin', [
            'crudAction' => 'new',
            'crudControllerFqcn' => ContactMessageCrudController::class,
        ]);

        self::assertGreaterThanOrEqual(400, $this->client->getResponse()->getStatusCode());
    }

    public function testGradeCrudIndexAndNewAreReachable(): void
    {
        $this->loginAsAdmin();

        $this->client->request('GET', '/admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => GradeCrudController::class,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Niveaux');

        $this->client->request('GET', '/admin', [
            'crudAction' => 'new',
            'crudControllerFqcn' => GradeCrudController::class,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name$="[name]"]');
        self::assertSelectorExists('input[name$="[weight]"]');
    }

    public function testContentThemeCrudIndexAndNewAreReachable(): void
    {
        $this->loginAsAdmin();

        $this->client->request('GET', '/admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => ContentThemeCrudController::class,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Thèmes');

        $this->client->request('GET', '/admin', [
            'crudAction' => 'new',
            'crudControllerFqcn' => ContentThemeCrudController::class,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name$="[name]"]');
        self::assertSelectorExists('input[name$="[icon]"]');
        self::assertSelectorExists('input[name$="[weight]"]');
    }

    public function testSchoolClassCrudIndexAndNewAreReachable(): void
    {
        $this->loginAsAdmin();

        $this->client->request('GET', '/admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => SchoolClassCrudController::class,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Classes');

        $this->client->request('GET', '/admin', [
            'crudAction' => 'new',
            'crudControllerFqcn' => SchoolClassCrudController::class,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name$="[name]"]');
        self::assertSelectorExists('select[name$="[grade]"]');
        self::assertSelectorExists('input[name$="[teacher]"]');
    }

    private function loginAsAdmin(): void
    {
        $user = (new User())
            ->setEmail('staff@example.com')
            ->setFirstName('Staff')
            ->setLastName('User')
            ->setRoles([UserRole::Admin->value])
            ->setIsActive(true);

        $user->setPassword(password_hash('changeme123', PASSWORD_BCRYPT));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request('GET', '/admin/login');
        $this->client->submitForm('Sign in', [
            '_username' => 'staff@example.com',
            '_password' => 'changeme123',
        ]);

        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();
    }
}
