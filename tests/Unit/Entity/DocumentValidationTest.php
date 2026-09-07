<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ContentTheme;
use App\Entity\Document;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DocumentValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testRejectsMissingFile(): void
    {
        $document = $this->validDocument();

        $violations = $this->validator->validate($document);

        self::assertGreaterThan(0, $violations->count());
        self::assertSame('file', $violations[0]->getPropertyPath());
    }

    public function testAcceptsExistingFilenameWithoutNewUpload(): void
    {
        $document = $this->validDocument()->setFilename('compte-rendu.pdf');

        $violations = $this->validator->validate($document);

        self::assertCount(0, $violations);
    }

    public function testRejectsBlankName(): void
    {
        $document = $this->validDocument()
            ->setFilename('compte-rendu.pdf')
            ->setName('');

        $violations = $this->validator->validate($document);

        self::assertGreaterThan(0, $violations->count());
        self::assertSame('name', $violations[0]->getPropertyPath());
    }

    private function validDocument(): Document
    {
        $theme = (new ContentTheme())
            ->setName('Association')
            ->setIcon('mdi-account-group')
            ->setWeight(20);

        return (new Document())
            ->setName('Compte rendu AG')
            ->setTheme($theme);
    }
}
