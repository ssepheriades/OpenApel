<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\SiteSettings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SiteSettingsSchoolYearTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testDefaultBoundsAreValid(): void
    {
        $violations = $this->validator->validate(new SiteSettings());

        self::assertCount(0, $violations);
    }

    public function testRejectsTheSameMonthAndDay(): void
    {
        $settings = (new SiteSettings())
            ->setSchoolYearStart(new \DateTimeImmutable('2000-08-01'))
            ->setSchoolYearEnd(new \DateTimeImmutable('2012-08-01'));

        $violations = $this->validator->validate($settings);

        self::assertCount(1, $violations);
        self::assertSame('schoolYearEnd', $violations[0]->getPropertyPath());
    }

    public function testAllowsWrappingBounds(): void
    {
        $settings = (new SiteSettings())
            ->setSchoolYearStart(new \DateTimeImmutable('2000-09-02'))
            ->setSchoolYearEnd(new \DateTimeImmutable('2000-06-30'));

        $violations = $this->validator->validate($settings);

        self::assertCount(0, $violations);
    }
}
