<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * How GET /media/{mapping}/{filename} may expose a file.
 * Staff means 200 only for ROLE_ADMIN, and never with a public cache.
 */
enum MediaDownloadAccess
{
    case Denied;
    case Public;
    case Staff;
}
