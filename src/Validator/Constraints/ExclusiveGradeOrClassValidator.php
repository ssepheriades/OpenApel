<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\AudienceTargetedInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ExclusiveGradeOrClassValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ExclusiveGradeOrClass) {
            throw new UnexpectedTypeException($constraint, ExclusiveGradeOrClass::class);
        }

        if (!$value instanceof AudienceTargetedInterface) {
            throw new UnexpectedTypeException($value, AudienceTargetedInterface::class);
        }

        if ($value->getGrades()->isEmpty() || $value->getSchoolClasses()->isEmpty()) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }
}
