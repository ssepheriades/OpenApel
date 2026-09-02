<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Entity\Faq;
use App\Entity\Grade;
use App\Entity\SchoolClass;
use App\Validator\Constraints\ExclusiveGradeOrClass;
use App\Validator\Constraints\ExclusiveGradeOrClassValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<ExclusiveGradeOrClassValidator>
 */
final class ExclusiveGradeOrClassValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ExclusiveGradeOrClassValidator
    {
        return new ExclusiveGradeOrClassValidator();
    }

    public function testEmptyTargetsAreValid(): void
    {
        $this->validator->validate(new Faq(), new ExclusiveGradeOrClass());

        $this->assertNoViolation();
    }

    public function testGradesOnlyAreValid(): void
    {
        $faq = (new Faq())->addGrade((new Grade())->setName('CE1'));

        $this->validator->validate($faq, new ExclusiveGradeOrClass());

        $this->assertNoViolation();
    }

    public function testSchoolClassesOnlyAreValid(): void
    {
        $faq = (new Faq())->addSchoolClass((new SchoolClass())->setName('CE1-A'));

        $this->validator->validate($faq, new ExclusiveGradeOrClass());

        $this->assertNoViolation();
    }

    public function testGradesAndSchoolClassesTogetherAreInvalid(): void
    {
        $constraint = new ExclusiveGradeOrClass();
        $faq = (new Faq())
            ->addGrade((new Grade())->setName('CE1'))
            ->addSchoolClass((new SchoolClass())->setName('CE1-A'));

        $this->validator->validate($faq, $constraint);

        $this->buildViolation($constraint->message)->assertRaised();
    }
}
