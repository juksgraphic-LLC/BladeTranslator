<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Exceptions;

/**
 * Raised when the AI stopped because it hit the output token limit.
 *
 * Distinct from other provider errors because the right reaction is not to
 * retry the same request but to send fewer items (see PageTranslator).
 */
final class ResponseTruncatedException extends ProviderException
{
    public static function create(): self
    {
        return new self('AI response was truncated (finish_reason=length). Reduce the batch size or raise max tokens.');
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
