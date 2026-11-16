<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\HealthController;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class HealthControllerTest extends TestCase
{
    public function testOkWhenDatabaseResponds(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('executeQuery')
            ->with('SELECT 1')
            ->willReturn($this->createMock(Result::class));

        $response = (new HealthController($connection))();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(
            ['status' => 'ok', 'database' => 'up'],
            json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR),
        );
    }

    public function testServiceUnavailableWhenDatabaseFails(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('executeQuery')
            ->willThrowException(new \RuntimeException('db down'));

        $response = (new HealthController($connection))();

        self::assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        self::assertSame(
            ['status' => 'error', 'database' => 'unavailable'],
            json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR),
        );
    }
}
