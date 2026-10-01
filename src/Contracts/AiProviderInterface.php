<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Contracts;

use Juksgraphic\BladeTranslator\Dto\AiResponse;
use Juksgraphic\BladeTranslator\Exceptions\ProviderException;

/**
 * Contract every AI vendor adapter must fulfil (OpenAI-compatible, Anthropic, Gemini...).
 *
 * Implementations own transport concerns: authentication, timeouts, retries,
 * and mapping the vendor payload to a normalized AiResponse.
 */
interface AiProviderInterface
{
    /**
     * Send one prompt pair to the model and return its normalized answer.
     *
     * @param string $system Instructions defining how the AI should behave.
     * @param string $user Actual content to process.
     *
     * @throws ProviderException When the request fails or the payload is unusable.
     */
    public function ask(string $system, string $user): AiResponse;
}
