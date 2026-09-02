<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\TeamMemberProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'TeamMember',
    operations: [
        new GetCollection(
            uriTemplate: '/team_members',
            paginationEnabled: false,
            normalizationContext: ['groups' => ['team_member:read']],
            provider: TeamMemberProvider::class,
        ),
    ],
)]
final readonly class TeamMember
{
    public function __construct(
        #[Groups(['team_member:read'])]
        public string $id,
        #[Groups(['team_member:read'])]
        public string $firstName,
        #[Groups(['team_member:read'])]
        public string $lastName,
        #[Groups(['team_member:read'])]
        public ?string $position,
        #[Groups(['team_member:read'])]
        public ?string $phone,
        #[Groups(['team_member:read'])]
        public ?string $shortBio,
        #[Groups(['team_member:read'])]
        public ?string $bio,
        #[Groups(['team_member:read'])]
        public ?string $photoUrl,
    ) {
    }
}
