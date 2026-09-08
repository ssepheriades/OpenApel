<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\SiteSettings;
use App\EventListener\NoindexHeaderListener;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class NoindexProtectionTest extends WebTestCase
{
    /**
     * @var list<string>
     */
    private const array AI_USER_AGENTS = [
        'GPTBot',
        'ChatGPT-User',
        'Google-Extended',
        'Google-CloudVertexBot',
        'Anthropic-AI',
        'ClaudeBot',
        'Claude-Web',
        'Claude-SearchBot',
        'Claude-User',
        'PerplexityBot',
        'Applebot-Extended',
        'Bytespider',
        'CCBot',
        'Amazonbot',
        'cohere-ai',
        'Diffbot',
        'FacebookBot',
        'Meta-ExternalAgent',
    ];

    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testRobotsTxtDisallowsAllCrawlers(): void
    {
        $this->client->request('GET', '/robots.txt');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/plain; charset=UTF-8');
        self::assertResponseHeaderSame('X-Robots-Tag', NoindexHeaderListener::HEADER_VALUE);

        $body = str_replace("\r\n", "\n", (string) $this->client->getResponse()->getContent());
        self::assertStringContainsString("User-agent: *\nDisallow: /", $body);

        foreach (self::AI_USER_AGENTS as $userAgent) {
            self::assertStringContainsString(
                "User-agent: {$userAgent}\nDisallow: /",
                $body,
            );
        }
    }

    public function testNotFoundResponseHasNoindexHeader(): void
    {
        $this->client->request('GET', '/register');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('X-Robots-Tag', NoindexHeaderListener::HEADER_VALUE);
    }

    public function testSpaHtmlHasNoindexMetaAndHeader(): void
    {
        $this->ensureSiteSettingsSchema();

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('X-Robots-Tag', NoindexHeaderListener::HEADER_VALUE);
        self::assertSelectorExists('meta[name="robots"][content="'.NoindexHeaderListener::HEADER_VALUE.'"]');
        self::assertSelectorExists('meta[name="googlebot"][content="'.NoindexHeaderListener::HEADER_VALUE.'"]');
    }

    private function ensureSiteSettingsSchema(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $schemaTool = new SchemaTool($entityManager);
        $metadata = [$entityManager->getClassMetadata(SiteSettings::class)];

        try {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        } catch (DbalException $exception) {
            self::markTestSkipped(sprintf('Database is not reachable for integration tests: %s', $exception->getMessage()));
        }
    }
}
