<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Page;
use App\Enum\PageSlug;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PageValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testDocumentRequiresABody(): void
    {
        $page = Page::fromSlug(PageSlug::MentionsLegales)->setBody(null);

        $violations = $this->validator->validate($page);

        self::assertGreaterThan(0, $violations->count());
        self::assertSame('body', $violations->get(0)->getPropertyPath());
    }

    public function testHomeAllowsAnEmptyBody(): void
    {
        $page = Page::fromSlug(PageSlug::Home)->setBody(null);

        $violations = $this->validator->validate($page);

        self::assertCount(0, $violations);
    }
}
