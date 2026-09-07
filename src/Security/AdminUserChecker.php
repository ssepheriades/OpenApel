<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AdminUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // Status and role checks belong in checkPostAuth so a wrong password
        // cannot be distinguished from an inactive or non-admin account.
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive() || !$user->hasRole(UserRole::Admin)) {
            throw new BadCredentialsException();
        }
    }
}
