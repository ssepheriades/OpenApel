<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $existingUser = $manager->getRepository(User::class)->findOneBy(['email' => 'staff@example.com']);

        if (null === $existingUser) {
            $user = (new User())
                ->setEmail('staff@example.com')
                ->setFirstName('Staff')
                ->setLastName('User')
                ->setRoles(['ROLE_STAFF'])
                ->setIsActive(true);

            $user->setPassword($this->passwordHasher->hashPassword($user, 'changeme'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
