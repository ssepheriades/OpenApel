<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ContactMessageResource;
use App\Service\ContactMessageService;

/**
 * @implements ProcessorInterface<ContactMessageResource, ContactMessageResource>
 */
final readonly class ContactMessageProcessor implements ProcessorInterface
{
    public function __construct(
        private ContactMessageService $contactMessageService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ContactMessageResource
    {
        if (!$data instanceof ContactMessageResource) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', ContactMessageResource::class, get_debug_type($data)));
        }

        $this->contactMessageService->submit($data);

        return new ContactMessageResource(ok: true);
    }
}
