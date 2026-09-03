<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Page;
use App\Entity\SiteSettings;
use App\Enum\PageSlug;
use App\Repository\PageRepository;
use App\Service\PageCatalogProvider;
use App\Service\SiteSettingsProvider;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PageApiTest extends WebTestCase
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
            $this->entityManager->getClassMetadata(SiteSettings::class),
            $this->entityManager->getClassMetadata(Page::class),
        ];
        $schemaTool = new SchemaTool($this->entityManager);

        try {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        } catch (DbalException $exception) {
            self::markTestSkipped(sprintf('Database is not reachable for integration tests: %s', $exception->getMessage()));
        }

        static::getContainer()->get(SiteSettingsProvider::class)->invalidate();
        static::getContainer()->get(PageCatalogProvider::class)->invalidate();
    }

    public function testPublicApiExposesTheCatalogueBySlug(): void
    {
        $repository = static::getContainer()->get(PageRepository::class);
        $pages = $repository->ensureCatalog();
        foreach ($pages as $page) {
            if (PageSlug::Faq === $page->getSlug()) {
                $page->setTitle('Questions des familles')->setSubtitle('Chapô FAQ');
            }
        }
        $this->entityManager->flush();
        static::getContainer()->get(PageCatalogProvider::class)->invalidate();

        $this->client->request('GET', '/api/pages', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseIsSuccessful();
        $collection = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($collection);
        self::assertCount(\count(PageSlug::cases()), $collection);
        self::assertSame('home', $collection[0]['slug']);
        self::assertArrayHasKey('body', $collection[0]);

        $this->client->request('GET', '/api/pages/faq', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseIsSuccessful();
        $faq = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('faq', $faq['slug']);
        self::assertSame('section', $faq['kind']);
        self::assertSame('Questions des familles', $faq['title']);
        self::assertSame('Chapô FAQ', $faq['subtitle']);
        self::assertTrue($faq['visible']);
        self::assertArrayNotHasKey('updatedAt', $faq);
    }

    public function testUnknownSlugIsNotFound(): void
    {
        static::getContainer()->get(PageRepository::class)->ensureCatalog();

        $this->client->request('GET', '/api/pages/does-not-exist', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testWritesAreNotExposed(): void
    {
        $this->client->request(
            'POST',
            '/api/pages',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: '{}',
        );

        self::assertResponseStatusCodeSame(405);
    }
}
