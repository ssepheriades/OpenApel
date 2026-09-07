<?php

declare(strict_types=1);

namespace App;

/**
 * Civil timezone for naive datetimes (events, posts, etc.).
 *
 * PostgreSQL stores TIMESTAMP WITHOUT TIME ZONE; PHP must hydrate them as
 * Europe/Paris so the API offset matches the clock time entered in EasyAdmin.
 */
final class AppTimezone
{
    public const string NAME = 'Europe/Paris';

    public static function apply(): void
    {
        date_default_timezone_set(self::NAME);
    }
}
