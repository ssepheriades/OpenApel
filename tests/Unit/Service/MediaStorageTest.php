<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Enum\MediaMapping;
use App\Service\MediaStorage;
use PHPUnit\Framework\TestCase;

final class MediaStorageTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/openapel-media-' . uniqid('', true);
        mkdir($this->root . '/demo/photos', 0775, true);
        file_put_contents($this->root . '/demo/photos/hero.webp', 'ok');
    }

    protected function tearDown(): void
    {
        $file = $this->root . '/demo/photos/hero.webp';
        if (is_file($file)) {
            unlink($file);
        }
        $dir = $this->root . '/demo/photos';
        if (is_dir($dir)) {
            rmdir($dir);
        }
        $instanceDir = $this->root . '/demo';
        if (is_dir($instanceDir)) {
            rmdir($instanceDir);
        }
        if (is_dir($this->root)) {
            rmdir($this->root);
        }
    }

    public function testPathResolvesInsideInstanceMapping(): void
    {
        $storage = new MediaStorage($this->root, 'demo');
        $file = $storage->path(MediaMapping::Photos, 'hero.webp');

        self::assertNotNull($file);
        self::assertSame('ok', file_get_contents($file->getPathname()));
        self::assertSame($this->root . '/demo/photos', $storage->directory(MediaMapping::Photos));
    }

    public function testPathRejectsTraversalAndUnknownFiles(): void
    {
        $storage = new MediaStorage($this->root, 'demo');

        self::assertNull($storage->path(MediaMapping::Photos, '..'));
        self::assertNull($storage->path(MediaMapping::Photos, '../hero.webp'));
        self::assertNull($storage->path(MediaMapping::Photos, 'missing.webp'));
        self::assertNull($storage->path(MediaMapping::Branding, 'hero.webp'));
    }

    public function testInvalidInstanceSlugIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new MediaStorage($this->root, '../etc');
    }
}
