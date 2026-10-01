<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Contracts;

use Juksgraphic\BladeTranslator\Exceptions\InvalidAiResponseException;

/**
 * Turns the raw text returned by the AI into a flat key => text map.
 */
interface ResponseParserInterface
{
    /**
     * @param string $raw Raw content of the AI response.
     * @return array<string, string>
     *
     * @throws InvalidAiResponseException When the content is not a flat map of strings.
     */
    public function parse(string $raw): array;
}