<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\EventListener\NoindexHeaderListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class NoindexHeaderListenerTest extends TestCase
{
    public function testSetsHeaderOnMainRequest(): void
    {
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response(),
        );

        (new NoindexHeaderListener())->onKernelResponse($event);

        self::assertSame(
            NoindexHeaderListener::HEADER_VALUE,
            $event->getResponse()->headers->get('X-Robots-Tag'),
        );
    }

    public function testSkipsSubRequest(): void
    {
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            new Response(),
        );

        (new NoindexHeaderListener())->onKernelResponse($event);

        self::assertNull($event->getResponse()->headers->get('X-Robots-Tag'));
    }
}
