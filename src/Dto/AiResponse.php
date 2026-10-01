<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Dto;

/**
 * Normalized answer returned by any AI provider.
 * Carrying metadata (not just text) lets the translator detect truncation
 * and lets callers track token usage and cost.
 */
final readonly class AiResponse
{
    /**
     * @param string $content Text content of the completion.
     * @param string|null $finishReason Provider stop reason (e.g. "stop", "length").
     * @param int|null $promptTokens Tokens consumed by the prompt, if reported.
     * @param int|null $completionTokens Tokens generated, if reported.
     * @param string|null $model Model that actually served the request, if reported.
     */
    public function __construct(
        public string $content,
        public ?string $finishReason  = null,
        public ?int $promptTokens     = null,
        public ?int $completionTokens = null,
        public ?string $model         = null,
    ) {}

    /**
     * Whether the output was cut because the token limit was reached.
     * @return bool
     */
    public function isTruncated(): bool
    {
        return in_array($this->finishReason, ['length', 'max_tokens'], true);
    }

    /**
     * Number of total tokens
     * @return int|null
     */
    public function totalTokens(): ?int
    {
        if ($this->promptTokens === null && $this->completionTokens === null) {
            return null;
        }

        return ($this->promptTokens ?? 0) + ($this->completionTokens ?? 0);
    }
}
