<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\SiteSettings;
use App\Entity\User;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminAuthenticationTest extends WebTestCase
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
            // The login page renders site_settings(), which needs the singleton table.
            $this->entityManager->getClassMetadata(SiteSettings::class),
        ];
        $schemaTool = new SchemaTool($this->entityManager);

        try {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        } catch (DbalException $exception) {
            self::markTestSkipped(sprintf('Database is not reachable for integration tests: %s', $exception->getMessage()));
        }
    }

    public function testLoginPageIsPublic(): void
    {
        $this->client->request('GET', '/admin/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'OpenApel Admin');
    }

    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin');

        self::assertResponseRedirects('/admin/login');
    }

    public function testStaffUserCanLogIn(): void
    {
        $this->createStaffUser();

        $crawler = $this->client->request('GET', '/admin/login');
        $this->client->submitForm('Sign in', [
            '_username' => 'staff@example.com',
            '_password' => 'changeme123',
        ]);

        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testInvalidCredentialsShowError(): void
    {
        $this->createStaffUser();

        $this->client->request('GET', '/admin/login');
        $this->client->submitForm('Sign in', [
            '_username' => 'staff@example.com',
            '_password' => 'wrong-password',
        ]);

        self::assertResponseRedirects('/admin/login');
        $crawler = $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Invalid credentials.', $crawler->filter('body')->text());
    }

    public function testInactiveStaffUserCannotLogIn(): void
    {
        $this->createStaffUser(false);

        $this->client->request('GET', '/admin/login');
        $this->client->submitForm('Sign in', [
            '_username' => 'staff@example.com',
            '_password' => 'changeme123',
        ]);

        self::assertResponseRedirects('/admin/login');
        $crawler = $this->client->followRedirect();

        self::assertStringContainsString('Your account has been disabled.', $crawler->filter('body')->text());
    }

    private function createStaffUser(bool $isActive = true): User
    {
        $user = (new User())
            ->setEmail('staff@example.com')
            ->setFirstName('Staff')
            ->setLastName('User')
            ->setRoles(['ROLE_STAFF'])
            ->setIsActive($isActive);

        $user->setPassword(password_hash('changeme123', PASSWORD_BCRYPT));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
