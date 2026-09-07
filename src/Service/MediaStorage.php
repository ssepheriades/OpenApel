<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\MediaMapping;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class MediaStorage
{
    private const string INSTANCE_SLUG = '/^[a-z0-9](?:[a-z0-9-]{0,62}[a-z0-9])?$/';
    private const string FILENAME = '/^[A-Za-z0-9._-]+$/';

    private readonly string $uploadDir;
    private readonly string $instance;

    public function __construct(
        #[Autowire('%app.upload_dir%')]
        string $uploadDir,
        #[Autowire('%app.instance%')]
        string $instance,
    ) {
        if (1 !== preg_match(self::INSTANCE_SLUG, $instance)) {
            throw new \InvalidArgumentException(sprintf(
                'APP_INSTANCE "%s" is invalid: use a lowercase slug (a-z, 0-9, hyphen).',
                $instance,
            ));
        }

        $this->instance = $instance;
        $this->uploadDir = rtrim($uploadDir, '/\\');
    }

    public function directory(MediaMapping $mapping): string
    {
        return $this->uploadDir . '/' . $this->instance . '/' . $mapping->value;
    }

    public function path(MediaMapping $mapping, string $filename): ?\SplFileInfo
    {
        if (1 !== preg_match(self::FILENAME, $filename)) {
            return null;
        }

        $directory = $this->directory($mapping);
        $fullPath = $directory . '/' . $filename;
        if (!is_file($fullPath) || !is_readable($fullPath)) {
            return null;
        }

        $realFile = realpath($fullPath);
        $realDir = realpath($directory);
        if (false === $realFile || false === $realDir) {
            return null;
        }

        $prefix = $realDir . \DIRECTORY_SEPARATOR;
        if (!str_starts_with($realFile, $prefix) && $realFile !== $realDir) {
            return null;
        }

        return new \SplFileInfo($realFile);
    }
}
