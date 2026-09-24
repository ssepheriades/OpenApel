<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\IcsEncoder;
use PHPUnit\Framework\TestCase;

final class IcsEncoderTest extends TestCase
{
    private IcsEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new IcsEncoder();
    }

    public function testTextEscapesSpecialCharacters(): void
    {
        self::assertSame(
            'AG\\, réunion\\; salle A\\\\\\nSuite',
            $this->encoder->text("AG, réunion; salle A\\\nSuite"),
        );
    }

    public function testFoldSplitsAtSeventyFiveOctets(): void
    {
        $line = 'SUMMARY:'.str_repeat('a', 80);
        $folded = $this->encoder->fold($line);

        self::assertStringContainsString("\r\n ", $folded);
        foreach (explode("\r\n", $folded) as $physical) {
            self::assertLessThanOrEqual(75, strlen($physical));
        }
    }

    public function testFoldDoesNotSplitMultibyteCharacters(): void
    {
        $line = 'SUMMARY:'.str_repeat('é', 50);
        $folded = $this->encoder->fold($line);

        foreach (explode("\r\n", str_replace("\r\n ", "\r\n", $folded)) as $physical) {
            $content = str_starts_with($physical, ' ') ? substr($physical, 1) : $physical;
            self::assertTrue(mb_check_encoding($content, 'UTF-8'));
        }
    }

    public function testDocumentUsesCrlf(): void
    {
        $document = $this->encoder->document(['BEGIN:VCALENDAR', 'END:VCALENDAR']);

        self::assertSame("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n", $document);
    }
}
