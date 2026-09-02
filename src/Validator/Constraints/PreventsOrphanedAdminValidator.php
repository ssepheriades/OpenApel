<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PreventsOrphanedAdminValidator extends ConstraintValidator
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PreventsOrphanedAdmin) {
            throw new UnexpectedTypeException($constraint, PreventsOrphanedAdmin::class);
        }

        if (!$value instanceof User) {
            throw new UnexpectedTypeException($value, User::class);
        }

        $isAdminAndActive = $value->hasRole(UserRole::Admin) && $value->isActive();

        if ($isAdminAndActive) {
            return;
        }

        $otherActiveAdminsCount = $this->userRepository->countActiveAdmins(excludeId: $value->getId());

        if (0 === $otherActiveAdminsCount) {
            $this->context->addViolation(
                'Impossible de retirer les droits administrateur ou de désactiver ce compte : c\'est le dernier administrateur actif.'
            );
        }
    }
}
