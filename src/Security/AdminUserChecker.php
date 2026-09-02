<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AdminUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && !$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('Your account has been disabled.');
        }

        if ($user instanceof User && !$user->hasRole(UserRole::Admin)) {
            throw new CustomUserMessageAccountStatusException('Ce compte n\'a pas accès à l\'administration.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
