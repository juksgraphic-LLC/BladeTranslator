<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Support;

use Juksgraphic\BladeTranslator\Exceptions\InvalidAiResponseException;

/**
 * Detects placeholders in a text and checks that a translation kept them intact.
 *
 * Recognized syntaxes: {{ $x }} (Blade echo), {name}, %s / %d / %1$s (printf), :name (Laravel).
 */
final class PlaceholderValidator
{
    /**
     * Order matters: Blade echoes are matched first so that ":" inside
     * "{{ $a ? $b : $c }}" is never mistaken for a ":name" placeholder.
     * ":name" must not follow a word character, ":" or "/" (avoids "Note:this" and "https://x").
     */
    private const string PATTERN = '/\{\{.*?\}\}|\{[A-Za-z_]\w*\}|%(?:\d+\$)?[sdfu]|(?<![\w:\/]):[A-Za-z_]\w*/s';

    /**
     * @return list<string> Placeholders found in the text, sorted (order may legitimately change when translating).
     */
    public function extract(string $text): array
    {
        preg_match_all(self::PATTERN, $text, $matches);

        $found = $matches[0];
        sort($found);

        return $found;
    }

    /**
     * @throws InvalidAiResponseException When the translation added, dropped or altered a placeholder.
     */
    public function assertSame(string $key, string $source, string $translated): void
    {
        $expected = $this->extract($source);
        $actual = $this->extract($translated);

        if ($expected !== $actual) {
            throw InvalidAiResponseException::placeholdersMismatch($key, $expected, $actual);
        }
    }
}
