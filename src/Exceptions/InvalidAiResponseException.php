<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Exceptions;

/**
 * Raised when the AI answered, but the content cannot be trusted or used.
 */
class InvalidAiResponseException extends TranslatorException
{
    /**
     * Not a json response
     * @param string $raw
     * @param string $reason
     * @return InvalidAiResponseException
     */
    public static function notJson(string $raw, string $reason = ''): self
    {
        $suffix = $reason === '' ? '' : " ({$reason})";

        return new self("AI response is not valid JSON{$suffix}: " . mb_substr($raw, 0, 300));
    }

    /**
     * Not a flad string map response
     * @param string|int $key
     * @return InvalidAiResponseException
     */
    public static function notFlatStringMap(string|int $key): self
    {
        return new self("Invalid translation entry for key '{$key}': expected a flat string value.");
    }

    /**
     * Keys is mismatch with the json response
     * @param list<string> $missing Keys sent but absent from the response.
     * @param list<string> $unexpected Keys returned but never sent.
     */
    public static function keysMismatch(array $missing, array $unexpected): self
    {
        $parts = [];

        if ($missing !== []) {
            $parts[] = 'missing: ' . implode(', ', $missing);
        }

        if ($unexpected !== []) {
            $parts[] = 'unexpected: ' . implode(', ', $unexpected);
        }

        return new self('AI response keys do not match the request (' . implode(' | ', $parts) . ').');
    }

    /**
     * Placeholders is mismatched
     * @param list<string> $expected Placeholders found in the source text.
     * @param list<string> $actual Placeholders found in the translated text.
     */
    public static function placeholdersMismatch(string $key, array $expected, array $actual): self
    {
        return new self(sprintf(
            "Placeholders differ for key '%s': expected [%s], got [%s].",
            $key,
            implode(', ', $expected),
            implode(', ', $actual),
        ));
    }
}
