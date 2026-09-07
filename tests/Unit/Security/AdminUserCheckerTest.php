<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Enum\UserRole;
use App\Security\AdminUserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

final class AdminUserCheckerTest extends TestCase
{
    public function testCheckPreAuthDoesNotRevealAccountStatus(): void
    {
        $checker = new AdminUserChecker();
        $user = (new User())
            ->setEmail('member@example.com')
            ->setRoles([UserRole::Member->value])
            ->setIsActive(false);

        $checker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }

    public function testInactiveAdminIsRejectedAfterPasswordCheck(): void
    {
        $checker = new AdminUserChecker();
        $user = (new User())
            ->setEmail('admin@example.com')
            ->setRoles([UserRole::Admin->value])
            ->setIsActive(false);

        $this->expectException(BadCredentialsException::class);

        $checker->checkPostAuth($user);
    }

    public function testMemberIsRejectedAfterPasswordCheck(): void
    {
        $checker = new AdminUserChecker();
        $user = (new User())
            ->setEmail('member@example.com')
            ->setRoles([UserRole::Member->value])
            ->setIsActive(true);

        $this->expectException(BadCredentialsException::class);

        $checker->checkPostAuth($user);
    }

    public function testActiveAdminPassesPostAuth(): void
    {
        $checker = new AdminUserChecker();
        $user = (new User())
            ->setEmail('admin@example.com')
            ->setRoles([UserRole::Admin->value])
            ->setIsActive(true);

        $checker->checkPostAuth($user);

        $this->addToAssertionCount(1);
    }
}
