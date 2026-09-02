<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\ContactMessage;
use App\Entity\Grade;
use App\Entity\SchoolClass;
use App\Entity\SiteSettings;
use App\Repository\ContactMessageRepository;
use App\Repository\SiteSettingsRepository;
use App\Service\SiteSettingsProvider;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

final class ContactMessageApiTest extends WebTestCase
{
    use MailerAssertionsTrait;

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
        $this->resetContactLimiter();
    }

    public function testPostCreatesMessageAndSendsEmail(): void
    {
        $this->configureContactEmail('contact@example.org');
        $schoolClass = $this->createSchoolClass();

        $this->client->request(
            'POST',
            '/api/contact_messages',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'name' => 'Marie Dupont',
                'email' => 'marie@example.org',
                'phone' => '0601020304',
                'subject' => 'Question cantine',
                'message' => 'Comment ça se passe pour la cantine ?',
                'schoolClassId' => $schoolClass->getId(),
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertTrue($payload['ok']);

        $messages = static::getContainer()->get(ContactMessageRepository::class)->findAll();
        self::assertCount(1, $messages);
        self::assertSame('Marie Dupont', $messages[0]->getName());
        self::assertSame('marie@example.org', $messages[0]->getEmail());
        self::assertSame($schoolClass->getId(), $messages[0]->getSchoolClass()?->getId());

        self::assertEmailCount(1);
        self::assertEmailHeaderSame(self::getMailerMessage(), 'To', 'contact@example.org');
        self::assertEmailHeaderSame(self::getMailerMessage(), 'Reply-To', 'Marie Dupont <marie@example.org>');
    }

    public function testPostRejectsInvalidPayload(): void
    {
        $this->configureContactEmail('contact@example.org');

        $this->client->request(
            'POST',
            '/api/contact_messages',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'name' => '',
                'email' => 'not-an-email',
                'subject' => '',
                'message' => '',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(422);
        self::assertCount(0, static::getContainer()->get(ContactMessageRepository::class)->findAll());
        self::assertEmailCount(0);
    }

    public function testPostHoneypotIsSilentlyDropped(): void
    {
        $this->configureContactEmail('contact@example.org');

        $this->client->request(
            'POST',
            '/api/contact_messages',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'name' => 'Bot',
                'email' => 'bot@example.org',
                'subject' => 'Spam',
                'message' => 'Buy now',
                'hp' => 'http://spam.example',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        self::assertCount(0, static::getContainer()->get(ContactMessageRepository::class)->findAll());
        self::assertEmailCount(0);
    }

    public function testCollectionIsNotExposed(): void
    {
        $this->client->request('GET', '/api/contact_messages', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertTrue(
            $this->client->getResponse()->getStatusCode() >= 400,
            'Public listing of contact messages must not be allowed.',
        );
    }

    public function testSixthPostIsRateLimited(): void
    {
        $payload = json_encode([
            'name' => 'Marie Dupont',
            'email' => 'marie@example.org',
            'subject' => 'Question',
            'message' => 'Bonjour',
        ], JSON_THROW_ON_ERROR);

        for ($i = 0; $i < 5; ++$i) {
            $this->client->request(
                'POST',
                '/api/contact_messages',
                server: [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ACCEPT' => 'application/json',
                ],
                content: $payload,
            );
            self::assertResponseStatusCodeSame(201);
        }

        $this->client->request(
            'POST',
            '/api/contact_messages',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: $payload,
        );

        self::assertResponseStatusCodeSame(429);
        self::assertTrue($this->client->getResponse()->headers->has('Retry-After'));
    }

    public function testCrossOriginDoesNotReceiveAllowOriginHeader(): void
    {
        $this->client->request(
            'OPTIONS',
            '/api/contact_messages',
            server: [
                'HTTP_ORIGIN' => 'https://evil.example',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type',
            ],
        );

        self::assertFalse($this->client->getResponse()->headers->has('Access-Control-Allow-Origin'));
    }

    private function resetContactLimiter(): void
    {
        $limiter = static::getContainer()->get('limiter.contact_form');
        self::assertInstanceOf(RateLimiterFactoryInterface::class, $limiter);
        $limiter->create('127.0.0.1')->reset();
        $limiter->create('unknown')->reset();
    }

    private function configureContactEmail(string $email): void
    {
        $settings = static::getContainer()->get(SiteSettingsRepository::class)->getOrCreate();
        $settings->setContactEmail($email);
        $this->entityManager->flush();
        static::getContainer()->get(SiteSettingsProvider::class)->invalidate();
    }

    private function createSchoolClass(): SchoolClass
    {
        $grade = (new Grade())->setName('CE1')->setWeight(10);
        $schoolClass = (new SchoolClass())->setName('CE1-A')->setGrade($grade);
        $this->entityManager->persist($grade);
        $this->entityManager->persist($schoolClass);
        $this->entityManager->flush();

        return $schoolClass;
    }
}
