<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

final class PublicApiSecurityTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->resetContactLimiter();
    }

    public function testRegisterRouteIsGone(): void
    {
        $this->client->request('GET', '/register');

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function gradeWriteRequests(): iterable
    {
        yield 'post collection' => ['POST', '/api/grades'];
        yield 'patch item' => ['PATCH', '/api/grades/1'];
        yield 'delete item' => ['DELETE', '/api/grades/1'];
        yield 'put item' => ['PUT', '/api/grades/1'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('gradeWriteRequests')]
    public function testGradeWritesAreNotExposed(string $method, string $uri): void
    {
        $this->client->request(
            $method,
            $uri,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: '{}',
        );

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function schoolClassWriteRequests(): iterable
    {
        yield 'post collection' => ['POST', '/api/school_classes'];
        yield 'patch item' => ['PATCH', '/api/school_classes/1'];
        yield 'delete item' => ['DELETE', '/api/school_classes/1'];
        yield 'put item' => ['PUT', '/api/school_classes/1'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('schoolClassWriteRequests')]
    public function testSchoolClassWritesAreNotAllowed(string $method, string $uri): void
    {
        $this->client->request(
            $method,
            $uri,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: '{}',
        );

        self::assertResponseStatusCodeSame(405);
    }

    public function testEventItemGetIsNotExposed(): void
    {
        $this->client->request('GET', '/api/events/1', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testSchoolClassItemGetIsNotExposed(): void
    {
        $this->client->request('GET', '/api/school_classes/1', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testSixthContactPostIsRateLimited(): void
    {
        $limiter = static::getContainer()->get('limiter.contact_form');
        self::assertInstanceOf(RateLimiterFactoryInterface::class, $limiter);
        $ipLimiter = $limiter->create('127.0.0.1');
        for ($i = 0; $i < 5; ++$i) {
            self::assertTrue($ipLimiter->consume()->isAccepted());
        }

        $this->client->request(
            'POST',
            '/api/contact_messages',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'name' => 'Marie Dupont',
                'email' => 'marie@example.org',
                'subject' => 'Question',
                'message' => 'Bonjour',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(429);
        self::assertTrue($this->client->getResponse()->headers->has('Retry-After'));
    }

    public function testCrossOriginDoesNotReceiveAllowOriginHeader(): void
    {
        $this->client->request(
            'OPTIONS',
            '/api/contact_messages',
            server: [
                'HTTP_ORIGIN' => 'https://evil.example',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type',
            ],
        );

        self::assertFalse($this->client->getResponse()->headers->has('Access-Control-Allow-Origin'));
    }

    private function resetContactLimiter(): void
    {
        $limiter = static::getContainer()->get('limiter.contact_form');
        self::assertInstanceOf(RateLimiterFactoryInterface::class, $limiter);
        $limiter->create('127.0.0.1')->reset();
        $limiter->create('unknown')->reset();
    }
}
