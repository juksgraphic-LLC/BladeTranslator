<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Providers;

use GuzzleHttp\ClientInterface;
use Juksgraphic\BladeTranslator\Dto\ProviderOptions;

/**
 * OpenAI (https://platform.openai.com/docs).
 *
 * Recent OpenAI models may reject "max_tokens" and a custom temperature; if so, use
 * new ProviderOptions(temperature: null, maxTokensField: 'max_completion_tokens').
 */
class OpenAiProvider extends OpenAiCompatibleProvider
{
    public const string BASE_URL = 'https://api.openai.com/v1';

    /**
     * @param string $apiKey OpenAI API key.
     * @param string $model Model identifier (required).
     */
    public function __construct(
        string $apiKey,
        string $model,
        ProviderOptions  $options = new ProviderOptions(),
        ?ClientInterface $client = null,
    ) {
        parent::__construct($apiKey, $model, self::BASE_URL, $options, $client);
    }
}
