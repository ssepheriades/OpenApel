<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Enum\UserRole;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

final class UserSerializationTest extends TestCase
{
    public function testUserWithPhotoFileCanBeSerializedForTheSession(): void
    {
        $user = (new User())
            ->setEmail('staff@example.test')
            ->setPassword('hashed-password')
            ->setRoles([UserRole::Admin->value])
            ->setFirstName('Ada')
            ->setPhotoFile(new File(__FILE__, false));

        $restored = unserialize(serialize($user));

        self::assertInstanceOf(User::class, $restored);
        self::assertSame('staff@example.test', $restored->getEmail());
        self::assertSame('hashed-password', $restored->getPassword());
        self::assertSame([UserRole::Admin->value], $restored->getRoles());
        self::assertSame('Ada', $restored->getFirstName());
        self::assertNull($restored->getPhotoFile());
        self::assertNull($restored->getPlainPassword());
        self::assertCount(0, $restored->getPosts());
    }
}
