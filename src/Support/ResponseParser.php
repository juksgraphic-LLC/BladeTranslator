<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Support;

use Juksgraphic\BladeTranslator\Contracts\ResponseParserInterface;
use Juksgraphic\BladeTranslator\Exceptions\InvalidAiResponseException;
use JsonException;

/**
 * Extracts a flat key => text map from raw AI output.
 *
 * Tolerates what models add despite instructions: reasoning blocks
 * (<think>...</think>), markdown fences, and text around the JSON object.
 */
final class ResponseParser implements ResponseParserInterface
{
    /**
     * Parsing the response returned by the AI provider
     * @param string $raw Raw string response returned by the AI
     * @return string[]
     */
    public function parse(string $raw): array
    {
        $json = $this->extractJsonObject($raw);

        try {

            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        } catch (JsonException $e) {
            throw InvalidAiResponseException::notJson($raw, $e->getMessage());
        }

        if (!is_array($data) || ($data !== [] && array_is_list($data))) {
            throw InvalidAiResponseException::notJson($raw, 'expected a JSON object');
        }

        $map = [];

        foreach ($data as $key => $value) {

            if (!is_string($value)) {
                throw InvalidAiResponseException::notFlatStringMap($key);
            }
            // PHP turns numeric-looking keys ("1") into ints when decoding.
            $map[(string) $key] = $value;
        }

        return $map;
    }

    /**
     * Keep everything between the first "{" and the last "}", after removing reasoning blocks.
     * @param string $raw Raw string response returned by the AI
     */
    private function extractJsonObject(string $raw): string
    {
        $text  = preg_replace('#<think>.*?</think>#si', '', $raw) ?? $raw;
        $text  = trim($text);
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');

        if ($start === false || $end === false || $end < $start) {
            throw InvalidAiResponseException::notJson($raw, 'no JSON object found');
        }

        return substr($text, $start, $end - $start + 1);
    }
}
