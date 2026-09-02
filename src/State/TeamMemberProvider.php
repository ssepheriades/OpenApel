<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\TeamMember;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\UrlHelper;

final class TeamMemberProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UrlHelper $urlHelper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        return array_map(
            fn ($user): TeamMember => new TeamMember(
                id: (string) $user->getId(),
                firstName: $user->getFirstName() ?? '',
                lastName: $user->getLastName() ?? '',
                position: $user->getPosition(),
                phone: $user->getPhone(),
                shortBio: $user->getShortBio(),
                bio: $user->getBio(),
                photoUrl: $this->resolvePhotoUrl($user),
            ),
            $this->userRepository->findActiveMembers(),
        );
    }

    private function resolvePhotoUrl($user): ?string
    {
        if (null === $user->getPhotoFilename()) {
            return null;
        }

        return $this->urlHelper->getAbsoluteUrl('/uploads/photos/' . $user->getPhotoFilename());
    }
}
