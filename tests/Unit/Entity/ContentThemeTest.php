<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ContentTheme;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ContentThemeTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidThemePassesPropertyConstraints(): void
    {
        $violations = $this->propertyViolations($this->validTheme());

        self::assertCount(0, $violations);
    }

    public function testRejectsBlankName(): void
    {
        $theme = $this->validTheme()->setName('');

        $violations = $this->propertyViolations($theme);

        self::assertGreaterThan(0, $violations->count());
        self::assertSame('name', $violations[0]->getPropertyPath());
    }

    public function testRejectsNonMdiIcon(): void
    {
        $theme = $this->validTheme()->setIcon('school');

        $violations = $this->propertyViolations($theme);

        self::assertCount(1, $violations);
        self::assertSame('icon', $violations[0]->getPropertyPath());
    }

    public function testToStringReturnsTheName(): void
    {
        self::assertSame('Pastorale', (string) $this->validTheme());
    }

    private function validTheme(): ContentTheme
    {
        return (new ContentTheme())
            ->setName('Pastorale')
            ->setIcon('mdi-hands-pray')
            ->setWeight(30);
    }

    private function propertyViolations(ContentTheme $theme): ConstraintViolationList
    {
        $violations = new ConstraintViolationList();
        $violations->addAll($this->validator->validateProperty($theme, 'name'));
        $violations->addAll($this->validator->validateProperty($theme, 'icon'));
        $violations->addAll($this->validator->validateProperty($theme, 'weight'));

        return $violations;
    }
}
