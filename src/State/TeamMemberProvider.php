<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\TeamMember;
use App\Entity\User;
use App\Enum\MediaMapping;
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
            fn (User $user): TeamMember => new TeamMember(
                id: (string) $user->getId(),
                firstName: $user->getFirstName() ?? '',
                lastName: $user->getLastName() ?? '',
                position: $user->getPosition(),
                photoUrl: $this->resolvePhotoUrl($user),
            ),
            $this->userRepository->findActiveMembers(),
        );
    }

    private function resolvePhotoUrl(User $user): ?string
    {
        $path = MediaMapping::Photos->url($user->getPhotoFilename());

        return null === $path ? null : $this->urlHelper->getAbsoluteUrl($path);
    }
}
