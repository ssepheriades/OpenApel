<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Post;
use App\Enum\MediaMapping;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

final class PostUploadTest extends TestCase
{
    public function testCoverImageDirtiesUpdatedAtAndExposesUrl(): void
    {
        $post = new Post();
        self::assertNull($post->getUpdatedAt());
        self::assertNull($post->getCoverImageUrl());

        $post->setCoverImageFile(new File(__FILE__, false));
        $post->setCoverImageFilename('actu.webp');

        self::assertInstanceOf(\DateTimeImmutable::class, $post->getUpdatedAt());
        self::assertSame(MediaMapping::Photos->url('actu.webp'), $post->getCoverImageUrl());
    }
}
