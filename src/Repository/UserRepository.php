<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function countActiveAdmins(?Uuid $excludeId = null): int
    {
        $admins = array_filter(
            $this->findBy(['isActive' => true]),
            static fn (User $user): bool => $user->hasRole(UserRole::Admin),
        );

        if (null !== $excludeId) {
            $admins = array_filter($admins, static fn (User $user): bool => !$user->getId()?->equals($excludeId));
        }

        return count($admins);
    }

    /**
     * @return User[]
     */
    public function findActiveMembers(): array
    {
        return array_values(array_filter(
            $this->findBy(['isActive' => true], ['weight' => 'DESC', 'lastName' => 'ASC']),
            static fn (User $user): bool => $user->hasRole(UserRole::Member),
        ));
    }
}
