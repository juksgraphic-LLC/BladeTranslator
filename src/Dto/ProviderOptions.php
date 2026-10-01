<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Dto;

use Juksgraphic\BladeTranslator\Exceptions\InvalidConfigurationException;

/**
 * Transport and generation settings for OpenAI-compatible providers.
 */
final readonly class ProviderOptions
{
    /**
     * @param float|null $temperature Sampling temperature. Null omits the field (some models only accept their default).
     * @param int|null $maxTokens Output token limit. Null lets the provider decide.
     * @param string $maxTokensField Name of the limit field: "max_tokens" (most vendors) or "max_completion_tokens" (recent OpenAI models).
     * @param bool $jsonResponseFormat Send response_format={"type":"json_object"}. Only enable if the vendor supports it.
     * @param int $timeout Per-request timeout in seconds.
     * @param int $maxRetries Retries after the first attempt, on network errors, 429 and 5xx.
     * @param int $retryBaseDelayMs Base delay of the exponential backoff, in milliseconds.
     * @param array<string, mixed> $extraPayload Vendor-specific fields merged last into the request body.
     *
     * @throws InvalidConfigurationException
     */
    public function __construct(
        public ?float $temperature        = 0.1,
        public ?int   $maxTokens          = null,
        public string $maxTokensField     = 'max_tokens',
        public bool   $jsonResponseFormat = false,
        public int    $timeout            = 180,
        public int    $maxRetries         = 3,
        public int    $retryBaseDelayMs   = 1000,
        public array  $extraPayload       = [],
    ) {
        if ($temperature !== null && ($temperature < 0.0 || $temperature > 2.0)) {
            throw InvalidConfigurationException::invalidValue('temperature', 'must be between 0 and 2');
        }

        if ($maxTokens !== null && $maxTokens < 1) {
            throw InvalidConfigurationException::invalidValue('maxTokens', 'must be at least 1');
        }

        if (trim($maxTokensField) === '') {
            throw InvalidConfigurationException::invalidValue('maxTokensField', 'must not be empty');
        }

        if ($timeout < 1) {
            throw InvalidConfigurationException::invalidValue('timeout', 'must be at least 1 second');
        }

        if ($maxRetries < 0) {
            throw InvalidConfigurationException::invalidValue('maxRetries', 'must not be negative');
        }

        if ($retryBaseDelayMs < 0) {
            throw InvalidConfigurationException::invalidValue('retryBaseDelayMs', 'must not be negative');
        }
    }
}
