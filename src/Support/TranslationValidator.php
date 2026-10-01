<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Support;

use Juksgraphic\BladeTranslator\Exceptions\InvalidAiResponseException;

/**
 * Verifies that an AI translation matches the request: same keys, same placeholders.
 */
final class TranslationValidator
{
    public function __construct(
        private readonly PlaceholderValidator $placeholders = new PlaceholderValidator(),
    ) {}

    /**
     * @param array<string, string> $source Map that was sent to the AI.
     * @param array<string, string> $translated Map returned by the AI.
     *
     * @throws InvalidAiResponseException
     */
    public function validate(array $source, array $translated): void
    {
        $sourceKeys     = array_map('strval', array_keys($source));
        $translatedKeys = array_map('strval', array_keys($translated));

        $missing    = array_values(array_diff($sourceKeys, $translatedKeys));
        $unexpected = array_values(array_diff($translatedKeys, $sourceKeys));

        if ($missing !== [] || $unexpected !== []) {
            throw InvalidAiResponseException::keysMismatch($missing, $unexpected);
        }

        foreach ($source as $key => $text) {
            $this->placeholders->assertSame((string) $key, $text, $translated[$key]);
        }
    }
}
