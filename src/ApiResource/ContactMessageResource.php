<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\ContactMessageProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'ContactMessage',
    operations: [
        new Post(
            uriTemplate: '/contact_messages',
            status: 201,
            processor: ContactMessageProcessor::class,
            read: false,
            denormalizationContext: ['groups' => ['contact:write']],
            normalizationContext: ['groups' => ['contact:read']],
        ),
    ],
)]
final readonly class ContactMessageResource
{
    public function __construct(
        #[Groups(['contact:write'])]
        #[Assert\NotBlank]
        #[Assert\Length(max: 180)]
        public string $name = '',
        #[Groups(['contact:write'])]
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public string $email = '',
        #[Groups(['contact:write'])]
        #[Assert\Length(max: 40)]
        public ?string $phone = null,
        #[Groups(['contact:write'])]
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $subject = '',
        #[Groups(['contact:write'])]
        #[Assert\NotBlank]
        #[Assert\Length(max: 5000)]
        public string $message = '',
        #[Groups(['contact:write'])]
        #[Assert\Positive]
        public ?int $schoolClassId = null,
        // Honeypot: real users leave this empty.
        #[ApiProperty(readable: false, writable: true)]
        #[Groups(['contact:write'])]
        public ?string $website = null,
        #[Groups(['contact:read'])]
        public bool $ok = true,
    ) {
    }
}
