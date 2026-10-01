<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Providers;

use GuzzleHttp\ClientInterface;
use Juksgraphic\BladeTranslator\Dto\ProviderOptions;

/**
 * Cerebras (https://inference-docs.cerebras.ai), OpenAI-compatible API.
 */
class CerebrasProvider extends OpenAiCompatibleProvider
{
    public const string BASE_URL = 'https://api.cerebras.ai/v1';

    /**
     * @param string $apiKey Cerebras API key.
     * @param string $model Model identifier (required).
     */
    public function __construct(
        string $apiKey,
        string $model,
        ProviderOptions $options = new ProviderOptions(),
        ?ClientInterface $client = null,
    ) {
        parent::__construct($apiKey, $model, self::BASE_URL, $options, $client);
    }
}
