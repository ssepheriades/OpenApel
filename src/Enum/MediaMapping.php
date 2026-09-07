<?php

declare(strict_types=1);

namespace App\Enum;

enum MediaMapping: string
{
    public const string ROUTE_REQUIREMENT = 'photos|branding|documents';
    public const string FILENAME_REQUIREMENT = '[A-Za-z0-9._-]+';

    case Photos = 'photos';
    case Branding = 'branding';
    case Documents = 'documents';

    public function isPublic(): bool
    {
        return true;
    }

    public function uriPrefix(): string
    {
        return '/media/' . $this->value;
    }

    public function url(?string $filename): ?string
    {
        if (null === $filename || '' === $filename) {
            return null;
        }

        return $this->uriPrefix() . '/' . $filename;
    }
}
