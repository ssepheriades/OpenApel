<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\ContentTheme;
use App\Entity\Document;
use App\Entity\Event;
use App\Entity\Grade;
use App\Entity\Post;
use App\Entity\SchoolClass;
use App\Entity\SiteSettings;
use App\Entity\User;
use App\Enum\DocumentVisibility;
use App\Enum\MediaMapping;
use App\Enum\PostState;
use App\Enum\UserRole;
use App\Service\MediaStorage;
use App\Service\SiteSettingsProvider;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class MediaControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $photoPath;
    private string $draftPhotoPath;
    private string $documentPath;
    private string $brandingSvgPath;
    private string $brandingPngPath;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        $storage = static::getContainer()->get(MediaStorage::class);
        $photosDir = $storage->directory(MediaMapping::Photos);
        $documentsDir = $storage->directory(MediaMapping::Documents);
        $brandingDir = $storage->directory(MediaMapping::Branding);
        if (!is_dir($photosDir)) {
            mkdir($photosDir, 0775, true);
        }
        if (!is_dir($documentsDir)) {
            mkdir($documentsDir, 0775, true);
        }
        if (!is_dir($brandingDir)) {
            mkdir($brandingDir, 0775, true);
        }

        $this->photoPath = $photosDir . '/fixture.txt';
        $this->draftPhotoPath = $photosDir . '/draft.txt';
        $this->documentPath = $documentsDir . '/secret.txt';
        $this->brandingSvgPath = $brandingDir . '/logo.svg';
        $this->brandingPngPath = $brandingDir . '/logo.png';
        file_put_contents($this->photoPath, 'photo-body');
        file_put_contents($this->draftPhotoPath, 'draft-body');
        file_put_contents($this->documentPath, 'secret-body');
        file_put_contents($this->brandingSvgPath, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        file_put_contents($this->brandingPngPath, 'png-body');
    }

    protected function tearDown(): void
    {
        foreach ([$this->photoPath, $this->draftPhotoPath, $this->documentPath, $this->brandingSvgPath, $this->brandingPngPath] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function testPublicPhotoIsServed(): void
    {
        $this->ensureMediaSchema();
        $this->persistPost('fixture.txt', PostState::Published);

        $this->client->request('GET', '/media/photos/fixture.txt');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame('photo-body', file_get_contents($response->getFile()->getPathname()));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertSame("default-src 'none'", $response->headers->get('Content-Security-Policy'));
        self::assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
        self::assertStringContainsString('public', (string) $response->headers->get('Cache-Control'));
    }

    public function testOrphanPhotoFileIsNotFound(): void
    {
        $this->ensureMediaSchema();

        $this->client->request('GET', '/media/photos/fixture.txt');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDraftPhotoIsNotFoundAnonymously(): void
    {
        $this->ensureMediaSchema();
        $this->persistPost('draft.txt', PostState::Draft);

        $this->client->request('GET', '/media/photos/draft.txt');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDraftPhotoIsServedPrivatelyToAdmin(): void
    {
        $this->ensureMediaSchema();
        $this->persistPost('draft.txt', PostState::Draft);
        $this->loginAsAdmin();

        $this->client->request('GET', '/media/photos/draft.txt');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame('draft-body', file_get_contents($response->getFile()->getPathname()));
        self::assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        self::assertStringNotContainsString('public', (string) $response->headers->get('Cache-Control'));
    }

    public function testLegacyBrandingSvgIsServedAsAttachment(): void
    {
        $this->ensureMediaSchema();
        $this->setCurrentLogoFilename('logo.svg');

        $this->client->request('GET', '/media/branding/logo.svg');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertSame("default-src 'none'", $response->headers->get('Content-Security-Policy'));
        self::assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
        self::assertStringNotContainsString('inline', (string) $response->headers->get('Content-Disposition'));
    }

    public function testBrandingRasterIsServedInlineWithCsp(): void
    {
        $this->ensureMediaSchema();
        $this->setCurrentLogoFilename('logo.png');

        $this->client->request('GET', '/media/branding/logo.png');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame('png-body', file_get_contents($response->getFile()->getPathname()));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertSame("default-src 'none'", $response->headers->get('Content-Security-Policy'));
        self::assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
        self::assertStringContainsString('public', (string) $response->headers->get('Cache-Control'));
    }

    public function testOrphanBrandingFileIsNotFound(): void
    {
        $this->ensureMediaSchema();

        $this->client->request('GET', '/media/branding/logo.png');

        self::assertResponseStatusCodeSame(404);
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

    public function testOrphanDocumentFileIsNotFound(): void
    {
        $this->ensureMediaSchema();

        $this->client->request('GET', '/media/documents/secret.txt');

        self::assertResponseStatusCodeSame(404);
    }

    public function testVisibleDocumentIsServedPrivately(): void
    {
        $this->ensureMediaSchema();
        $this->persistDocument('secret.txt', DocumentVisibility::Visible);

        $this->client->request('GET', '/media/documents/secret.txt');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame('secret-body', file_get_contents($response->getFile()->getPathname()));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        self::assertStringNotContainsString('public', (string) $response->headers->get('Cache-Control'));
    }

    public function testHiddenDocumentIsNotFoundAnonymously(): void
    {
        $this->ensureMediaSchema();
        $this->persistDocument('secret.txt', DocumentVisibility::Hidden);

        $this->client->request('GET', '/media/documents/secret.txt');

        self::assertResponseStatusCodeSame(404);
    }

    public function testHiddenDocumentIsServedToAdmin(): void
    {
        $this->ensureMediaSchema();
        $this->persistDocument('secret.txt', DocumentVisibility::Hidden);
        $this->loginAsAdmin();

        $this->client->request('GET', '/media/documents/secret.txt');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame('secret-body', file_get_contents($response->getFile()->getPathname()));
        self::assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
    }

    private function ensureMediaSchema(): EntityManagerInterface
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $metadata = [
            $entityManager->getClassMetadata(User::class),
            $entityManager->getClassMetadata(SiteSettings::class),
            $entityManager->getClassMetadata(ContentTheme::class),
            $entityManager->getClassMetadata(Document::class),
            $entityManager->getClassMetadata(Grade::class),
            $entityManager->getClassMetadata(SchoolClass::class),
            $entityManager->getClassMetadata(Post::class),
            $entityManager->getClassMetadata(Event::class),
        ];
        $schemaTool = new SchemaTool($entityManager);

        try {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        } catch (DbalException $exception) {
            self::markTestSkipped(sprintf('Database is not reachable for integration tests: %s', $exception->getMessage()));
        }

        static::getContainer()->get(SiteSettingsProvider::class)->invalidate();
        static::getContainer()->get(SiteSettingsProvider::class)->getEntity();

        return $entityManager;
    }

    private function persistDocument(string $filename, DocumentVisibility $visibility): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $theme = (new ContentTheme())
            ->setName('Association')
            ->setIcon('mdi-account-group')
            ->setWeight(20);
        $document = (new Document())
            ->setName('Compte rendu')
            ->setTheme($theme)
            ->setFilename($filename)
            ->setVisibility($visibility);

        $entityManager->persist($theme);
        $entityManager->persist($document);
        $entityManager->flush();
    }

    private function persistPost(string $filename, PostState $state): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $author = (new User())
            ->setEmail('author@example.com')
            ->setFirstName('Auteur')
            ->setLastName('Test')
            ->setRoles([UserRole::Admin->value])
            ->setIsActive(true);
        $author->setPassword(password_hash('unused', PASSWORD_BCRYPT));

        $theme = (new ContentTheme())
            ->setName('Actualités')
            ->setIcon('mdi-newspaper')
            ->setWeight(10);

        $now = new \DateTimeImmutable();
        $post = (new Post())
            ->setTitle('Couverture')
            ->setContent('Contenu')
            ->setAuthor($author)
            ->setTheme($theme)
            ->setState($state)
            ->setCreatedAt($now)
            ->setUpdatedAt($now)
            ->setViewCount(0)
            ->setCoverImageFilename($filename);

        $entityManager->persist($author);
        $entityManager->persist($theme);
        $entityManager->persist($post);
        $entityManager->flush();
    }

    private function setCurrentLogoFilename(string $filename): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $provider = static::getContainer()->get(SiteSettingsProvider::class);
        $settings = $provider->getEntity();
        $settings->setLogoFilename($filename);
        $entityManager->flush();
        $provider->invalidate();
    }

    private function loginAsAdmin(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = (new User())
            ->setEmail('staff@example.com')
            ->setFirstName('Staff')
            ->setLastName('User')
            ->setRoles([UserRole::Admin->value])
            ->setIsActive(true);
        $user->setPassword(password_hash('changeme123', PASSWORD_BCRYPT));

        $entityManager->persist($user);
        $entityManager->flush();

        $this->client->request('GET', '/admin/login');
        $this->client->submitForm('Sign in', [
            '_username' => 'staff@example.com',
            '_password' => 'changeme123',
        ]);

        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();
    }
}
