<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Enum\MediaMapping;
use App\Service\MediaStorage;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class MediaControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $photoPath;
    private string $documentPath;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        $storage = static::getContainer()->get(MediaStorage::class);
        $photosDir = $storage->directory(MediaMapping::Photos);
        $documentsDir = $storage->directory(MediaMapping::Documents);
        if (!is_dir($photosDir)) {
            mkdir($photosDir, 0775, true);
        }
        if (!is_dir($documentsDir)) {
            mkdir($documentsDir, 0775, true);
        }

        $this->photoPath = $photosDir . '/fixture.txt';
        $this->documentPath = $documentsDir . '/secret.txt';
        file_put_contents($this->photoPath, 'photo-body');
        file_put_contents($this->documentPath, 'secret-body');
    }

    protected function tearDown(): void
    {
        foreach ([$this->photoPath, $this->documentPath] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function testPublicPhotoIsServed(): void
    {
        $this->client->request('GET', '/media/photos/fixture.txt');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame('photo-body', file_get_contents($response->getFile()->getPathname()));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function testMissingFileIsNotFound(): void
    {
        $this->client->request('GET', '/media/photos/missing.txt');

        self::assertResponseStatusCodeSame(404);
    }

    public function testTraversalIsNotFound(): void
    {
        $this->client->request('GET', '/media/photos/..');

        self::assertResponseStatusCodeSame(404);
    }

    public function testPublicDocumentIsServed(): void
    {
        $this->client->request('GET', '/media/documents/secret.txt');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame('secret-body', file_get_contents($response->getFile()->getPathname()));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }
}
