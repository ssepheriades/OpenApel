<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\UserCrudController;
use App\Entity\ContactMessage;
use App\Entity\Grade;
use App\Entity\SchoolClass;
use App\Entity\SiteSettings;
use App\Entity\User;
use App\Enum\UserRole;
use App\Service\SiteSettingsProvider;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

final class UserPasswordResetTest extends WebTestCase
{
    private const string ADMIN_EMAIL = 'staff@example.com';
    private const string ADMIN_PASSWORD = 'changeme123';
    private const string TARGET_EMAIL = 'member@example.com';
    private const string TARGET_OLD_PASSWORD = 'old-password-123';
    private const string TARGET_NEW_PASSWORD = 'new-secret-123';

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

    public function testResetPasswordFormIsReachableForAdmin(): void
    {
        $target = $this->createUser(self::TARGET_EMAIL, self::TARGET_OLD_PASSWORD);
        $this->loginAsAdmin();

        $crawler = $this->client->request('GET', $this->resetPasswordUrl($target));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1.title', 'Réinitialiser le mot de passe');
        self::assertSelectorExists('input[type="password"][name$="[plainPassword]"]');
        self::assertSelectorTextContains('body', $target->getEmail());
        self::assertSame(1, $crawler->filter('button[type="submit"]')->count());
    }

    public function testSuccessfulResetAllowsLoginWithNewPasswordOnly(): void
    {
        $target = $this->createUser(self::TARGET_EMAIL, self::TARGET_OLD_PASSWORD);
        $targetId = $target->getId();
        self::assertInstanceOf(Uuid::class, $targetId);

        $this->loginAsAdmin();

        $crawler = $this->client->request('GET', $this->resetPasswordUrl($target));
        self::assertResponseIsSuccessful();

        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'reset_user_password[plainPassword]' => self::TARGET_NEW_PASSWORD,
        ]));

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $reloaded = $this->entityManager->find(User::class, $targetId);
        self::assertInstanceOf(User::class, $reloaded);

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($reloaded, self::TARGET_NEW_PASSWORD));
        self::assertFalse($hasher->isPasswordValid($reloaded, self::TARGET_OLD_PASSWORD));

        $this->client->request('GET', '/admin/logout');
        $this->client->followRedirect();

        $this->client->request('GET', '/admin/login');
        $this->client->submitForm('Sign in', [
            '_username' => self::TARGET_EMAIL,
            '_password' => self::TARGET_NEW_PASSWORD,
        ]);
        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();

        $this->client->request('GET', '/admin/logout');
        $this->client->followRedirect();

        $this->client->request('GET', '/admin/login');
        $this->client->submitForm('Sign in', [
            '_username' => self::TARGET_EMAIL,
            '_password' => self::TARGET_OLD_PASSWORD,
        ]);
        self::assertResponseRedirects('/admin/login');
        $crawler = $this->client->followRedirect();
        self::assertStringContainsString('Invalid credentials.', $crawler->filter('body')->text());
    }

    public function testRejectedPasswordsDoNotChangeTheHash(): void
    {
        $target = $this->createUser(self::TARGET_EMAIL, self::TARGET_OLD_PASSWORD);
        $targetId = $target->getId();
        self::assertInstanceOf(Uuid::class, $targetId);

        $this->loginAsAdmin();
        $url = $this->resetPasswordUrl($target);

        $crawler = $this->client->request('GET', $url);
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'reset_user_password[plainPassword]' => 'short',
        ]));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->request('GET', $url);
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'reset_user_password[plainPassword]' => '',
        ]));
        self::assertResponseIsSuccessful();

        $this->entityManager->clear();
        $reloaded = $this->entityManager->find(User::class, $targetId);
        self::assertInstanceOf(User::class, $reloaded);

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($reloaded, self::TARGET_OLD_PASSWORD));
    }

    private function resetPasswordUrl(User $user): string
    {
        $id = $user->getId();
        self::assertInstanceOf(Uuid::class, $id);

        return static::getContainer()->get(AdminUrlGenerator::class)
            ->unsetAll()
            ->setDashboard(DashboardController::class)
            ->setController(UserCrudController::class)
            ->setAction('resetPassword')
            ->setEntityId((string) $id)
            ->generateUrl();
    }

    private function loginAsAdmin(): void
    {
        $this->createUser(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);

        $this->client->request('GET', '/admin/login');
        $this->client->submitForm('Sign in', [
            '_username' => self::ADMIN_EMAIL,
            '_password' => self::ADMIN_PASSWORD,
        ]);

        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();
    }

    private function createUser(string $email, string $plainPassword): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setFirstName('Test')
            ->setLastName('User')
            ->setRoles([UserRole::Admin->value])
            ->setIsActive(true);

        $user->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
