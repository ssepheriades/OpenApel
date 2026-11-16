<?php

declare(strict_types=1);

namespace App\Enum;

enum DocumentVisibility: string
{
    case Visible = 'visible';
    case Hidden = 'hidden';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Visible => 'Visible',
            self::Hidden => 'Masqué',
            self::Archived => 'Archivé',
        };
    }

    /** Listed in GET /api/documents. */
    public function isListed(): bool
    {
        return self::Visible === $this;
    }

    /** Served on GET /media/documents/{filename} without ROLE_ADMIN. */
    public function isPubliclyDownloadable(): bool
    {
        return match ($this) {
            self::Visible, self::Hidden => true,
            self::Archived => false,
        };
    }
}
