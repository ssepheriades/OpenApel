<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

final class ContactRateLimitListener
{
    public function __construct(
        #[Autowire(service: 'limiter.contact_form')]
        private readonly RateLimiterFactoryInterface $contactFormLimiter,
    ) {
    }

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 8)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ('POST' !== $request->getMethod() || !str_starts_with($request->getPathInfo(), '/api/contact_messages')) {
            return;
        }

        $limit = $this->contactFormLimiter->create($request->getClientIp() ?? 'unknown')->consume();
        if ($limit->isAccepted()) {
            return;
        }

        $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();

        throw new TooManyRequestsHttpException(
            $retryAfter > 0 ? $retryAfter : null,
            'Too many contact submissions.',
        );
    }
}
