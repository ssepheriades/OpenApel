<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\AppTimezone;
use PHPUnit\Framework\TestCase;

final class AppTimezoneTest extends TestCase
{
    public function testApplicationTimezoneIsParis(): void
    {
        AppTimezone::apply();

        self::assertSame('Europe/Paris', date_default_timezone_get());
        self::assertSame('Europe/Paris', AppTimezone::NAME);
    }
}
