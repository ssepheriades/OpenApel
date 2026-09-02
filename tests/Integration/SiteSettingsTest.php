<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\SiteSettings;
use App\Repository\SiteSettingsRepository;
use App\Service\SiteSettingsProvider;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SiteSettingsTest extends WebTestCase
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

        $metadata = [$this->entityManager->getClassMetadata(SiteSettings::class)];
        $schemaTool = new SchemaTool($this->entityManager);

        try {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        } catch (DbalException $exception) {
            self::markTestSkipped(sprintf('Database is not reachable for integration tests: %s', $exception->getMessage()));
        }

        static::getContainer()->get(SiteSettingsProvider::class)->invalidate();
    }

    public function testGetOrCreateIsIdempotent(): void
    {
        $repository = static::getContainer()->get(SiteSettingsRepository::class);

        $first = $repository->getOrCreate();
        $second = $repository->getOrCreate();

        self::assertNotNull($first->getId());
        self::assertSame($first->getId(), $second->getId());
        self::assertCount(1, $repository->findAll());
    }

    public function testPublicApiExposesSettings(): void
    {
        $settings = static::getContainer()->get(SiteSettingsRepository::class)->getOrCreate();
        $settings->setSiteName('APEL Démo')
            ->setBaseline('Ensemble pour nos enfants')
            ->setHomeTitle('Bienvenue à l\'école')
            ->setHomeText('**Ensemble** pour nos enfants.')
            ->setLogoFilename('logo.png');
        $this->entityManager->flush();

        $this->client->request('GET', '/api/site_settings', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('APEL Démo', $payload['siteName']);
        self::assertSame('Ensemble pour nos enfants', $payload['baseline']);
        self::assertSame('Bienvenue à l\'école', $payload['homeTitle']);
        self::assertSame('**Ensemble** pour nos enfants.', $payload['homeText']);
        self::assertSame('http://localhost/uploads/branding/logo.png', $payload['logoUrl']);
        self::assertNull($payload['faviconUrl']);
        self::assertSame(SiteSettings::DEFAULT_PRIMARY_COLOR, $payload['primaryColor']);
        self::assertSame(SiteSettings::DEFAULT_SCHOOL_YEAR_START, $payload['schoolYearStart']);
        self::assertSame(SiteSettings::DEFAULT_SCHOOL_YEAR_END, $payload['schoolYearEnd']);
        self::assertTrue($payload['faqVisible']);
        self::assertTrue($payload['teamVisible']);
        self::assertTrue($payload['postsVisible']);
        self::assertTrue($payload['agendaVisible']);
        self::assertArrayNotHasKey('updatedAt', $payload);
    }
}
