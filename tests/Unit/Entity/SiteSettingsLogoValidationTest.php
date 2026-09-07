<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\SiteSettings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SiteSettingsLogoValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testRejectsSvgLogo(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'logo');
        self::assertNotFalse($path);
        $svgPath = $path . '.svg';
        self::assertTrue(rename($path, $svgPath));
        file_put_contents($svgPath, '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        try {
            $settings = (new SiteSettings())->setLogoFile(new File($svgPath));
            $violations = $this->validator->validate($settings);
        } finally {
            if (is_file($svgPath)) {
                unlink($svgPath);
            }
        }

        self::assertGreaterThan(0, $violations->count());
        self::assertSame('logoFile', $violations[0]->getPropertyPath());
    }
}
