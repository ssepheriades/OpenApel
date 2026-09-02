<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ExclusiveGradeOrClass extends Constraint
{
    public string $message = 'Choisir des niveaux ou des classes, pas les deux.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
