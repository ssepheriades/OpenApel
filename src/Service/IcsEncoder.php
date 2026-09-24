<?php

declare(strict_types=1);

namespace App\Service;

/**
 * RFC 5545 text escaping and line folding (75-octet lines).
 */
final class IcsEncoder
{
    /**
     * @param list<string> $lines Unfolded or already-folded property lines, without CRLF
     */
    public function document(array $lines): string
    {
        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * @param array<string, string> $params
     */
    public function property(string $name, string $value, array $params = []): string
    {
        $prefix = $name;
        foreach ($params as $paramName => $paramValue) {
            $prefix .= ';'.$paramName.'='.$paramValue;
        }

        return $this->fold($prefix.':'.$value);
    }

    public function text(string $value): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $value);

        return str_replace(
            ['\\', ';', ',', "\n"],
            ['\\\\', '\\;', '\\,', '\\n'],
            $normalized,
        );
    }

    public function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $first = $this->utf8Prefix($line, 75);
        $rest = substr($line, strlen($first));
        $folded = $first;

        while ('' !== $rest) {
            $chunk = $this->utf8Prefix($rest, 74);
            $folded .= "\r\n ".$chunk;
            $rest = substr($rest, strlen($chunk));
        }

        return $folded;
    }

    private function utf8Prefix(string $value, int $maxBytes): string
    {
        if (strlen($value) <= $maxBytes) {
            return $value;
        }

        $chunk = substr($value, 0, $maxBytes);
        while ('' !== $chunk && !mb_check_encoding($chunk, 'UTF-8')) {
            $chunk = substr($chunk, 0, -1);
        }

        return $chunk;
    }
}
