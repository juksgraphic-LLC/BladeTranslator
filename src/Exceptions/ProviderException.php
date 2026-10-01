<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Exceptions;

use Throwable;

/**
 * Raised when communication with an AI provider fails.
 */
class ProviderException extends TranslatorException
{
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        public readonly ?int $retryAfter = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Throw exception when the request is failing
     * @param string $reason
     * @param mixed $previous
     * @return ProviderException
     */
    public static function requestFailed(string $reason, ?Throwable $previous = null): self
    {
        return new self(
            "AI request failed: {$reason}",
            previous: $previous
        );
    }

    /**
     * Throw error when an http error occurs
     * @param int $statusCode
     * @param string $body
     * @param mixed $previous
     * @return ProviderException
     */
    public static function httpError(int $statusCode, string $body = '', ?Throwable $previous = null): self
    {
        $excerpt = $body === '' ? '' : ' - ' . mb_substr($body, 0, 300);

        return new self(
            "AI provider returned HTTP {$statusCode}{$excerpt}",
            statusCode: $statusCode,
            previous: $previous
        );
    }

    /**
     * Throw error when rate limiting occurs
     * @param int|null $retryAfter Seconds to wait before retrying, when the provider tells us.
     */
    public static function rateLimited(?int $retryAfter = null, ?Throwable $previous = null): self
    {
        return new self(
            'AI provider rate limit reached.',
            statusCode: 429,
            retryAfter: $retryAfter,
            previous: $previous
        );
    }

    /**
     * Throw error when a malformed response is thrown by the AI
     * @param string $body
     * @return ProviderException
     */
    public static function malformedResponse(string $body): self
    {
        return new self('AI provider returned a malformed response: ' . mb_substr($body, 0, 300));
    }

    /**
     * Throw error when a truncated response is found
     * @return ProviderException
     */
    public static function truncated(): self
    {
        return new self('AI response was truncated (finish_reason=length). Reduce the batch size or raise max tokens.');
    }

    /**
     * Whether retrying the same request can reasonably succeed.
     */
    public function isRetryable(): bool
    {
        return $this->statusCode === null
            || $this->statusCode === 429
            || $this->statusCode >= 500;
    }
}
