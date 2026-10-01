<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Juksgraphic\BladeTranslator\Contracts\AiProviderInterface;
use Juksgraphic\BladeTranslator\Dto\AiResponse;
use Juksgraphic\BladeTranslator\Dto\ProviderOptions;
use Juksgraphic\BladeTranslator\Exceptions\InvalidConfigurationException;
use Juksgraphic\BladeTranslator\Exceptions\ProviderException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * Provider for any vendor exposing an OpenAI-compatible /chat/completions endpoint
 * (OpenAI, Cerebras, Groq, OpenRouter, Together, Ollama, Azure...).
 *
 * Use it directly with a custom base URL, or extend it to pre-fill a vendor's URL.
 *
 * Retries network errors, HTTP 429 and 5xx with exponential backoff (honouring Retry-After).
 * A truncated completion is NOT an error here: it is returned with finishReason "length"
 * so the caller can decide to split its batch.
 */
class OpenAiCompatibleProvider implements AiProviderInterface
{
    private const int MAX_RETRY_DELAY_MS = 60_000;

    protected readonly string $endpoint;

    protected readonly ClientInterface $client;

    /**
     * @param string $apiKey Bearer token. May be empty for keyless servers (e.g. local Ollama).
     * @param string $model Model identifier (required, vendors rename theirs regularly).
     * @param string $baseUrl Base URL of the API, e.g. "https://api.openai.com/v1".
     * @param ProviderOptions $options Generation and retry settings.
     * @param ClientInterface|null $client Guzzle client; inject one to customize transport or mock it in tests.
     *
     * @throws InvalidConfigurationException
     */
    public function __construct(
        protected readonly string $apiKey,
        protected readonly string $model,
        string $baseUrl,
        protected readonly ProviderOptions $options = new ProviderOptions(),
        ?ClientInterface $client = null,
    ) {
        if (trim($model) === '') {
            throw InvalidConfigurationException::invalidValue('model', 'must not be empty');
        }

        if (trim($baseUrl) === '') {
            throw InvalidConfigurationException::emptyPath('baseUrl');
        }

        $this->endpoint = rtrim($baseUrl, '/') . '/chat/completions';
        $this->client   = $client ?? new Client();
    }

    public function ask(string $system, string $user): AiResponse
    {
        $attempt = 0;

        while (true) {
            
            try {
                return $this->attempt($system, $user);
            } catch (ProviderException $e) {

                if ($attempt >= $this->options->maxRetries || !$e->isRetryable()) {
                    throw $e;
                }

                $this->pause($this->delayFor($attempt, $e));
                $attempt++;
            }
        }
    }

    /**
     * Wait before a retry. Overridable so tests do not actually sleep.
     */
    protected function pause(int $milliseconds): void
    {
        if ($milliseconds > 0) {
            usleep($milliseconds * 1000);
        }
    }

    /**
     * Delay before retry number $attempt + 1: Retry-After when given, otherwise exponential backoff with jitter.
     */
    protected function delayFor(int $attempt, ProviderException $e): int
    {
        if ($e->retryAfter !== null) {
            return min($e->retryAfter * 1000, self::MAX_RETRY_DELAY_MS);
        }

        $delay = $this->options->retryBaseDelayMs * (2 ** $attempt) + random_int(0, 250);

        return min($delay, self::MAX_RETRY_DELAY_MS);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(string $system, string $user): array
    {
        $payload = [
            'model'    => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
        ];

        if ($this->options->temperature !== null) {
            $payload['temperature'] = $this->options->temperature;
        }

        if ($this->options->maxTokens !== null) {
            $payload[$this->options->maxTokensField] = $this->options->maxTokens;
        }

        if ($this->options->jsonResponseFormat) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        return array_replace($payload, $this->options->extraPayload);
    }

    /**
     * @return array<string, string>
     */
    protected function headers(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        if ($this->apiKey !== '') {
            $headers['Authorization'] = "Bearer {$this->apiKey}";
        }

        return $headers;
    }

    /**
     * One HTTP round trip, without any retry.
     *
     * @throws ProviderException
     */
    private function attempt(string $system, string $user): AiResponse
    {
        try {
            $response = $this->client->request('POST', $this->endpoint, [
                'headers'     => $this->headers(),
                'json'        => $this->payload($system, $user),
                'timeout'     => $this->options->timeout,
                'http_errors' => false,
            ]);
            
        } catch (GuzzleException $e) {
            throw ProviderException::requestFailed($e->getMessage(), $e);
        }

        $status = $response->getStatusCode();
        $body   = (string) $response->getBody();

        if ($status === 429) {
            throw ProviderException::rateLimited($this->retryAfter($response));
        }

        if ($status >= 400) {
            throw ProviderException::httpError($status, $body, $this->retryAfter($response));
        }

        return $this->toAiResponse($body);
    }

    /**
     * @throws ProviderException When the body does not look like a chat completion.
     */
    private function toAiResponse(string $body): AiResponse
    {
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ProviderException::malformedResponse($body);
        }

        $choice  = is_array($data)   ? ($data['choices'][0] ?? null) : null;
        $content = is_array($choice) ? ($choice['message']['content'] ?? null) : null;

        if (!is_string($content)) {
            throw ProviderException::malformedResponse($body);
        }

        $finishReason = $choice['finish_reason'] ?? null;
        $model        = $data['model'] ?? null;

        return new AiResponse(
            content: $content,
            finishReason: is_string($finishReason) ? $finishReason : null,
            promptTokens: $this->intOrNull($data['usage']['prompt_tokens'] ?? null),
            completionTokens: $this->intOrNull($data['usage']['completion_tokens'] ?? null),
            model: is_string($model) ? $model : null,
        );
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }

    /**
     * Seconds from the Retry-After header (either a number of seconds or an HTTP date).
     */
    private function retryAfter(ResponseInterface $response): ?int
    {
        $header = trim($response->getHeaderLine('Retry-After'));

        if ($header === '') {
            return null;
        }

        if (ctype_digit($header)) {
            return (int) $header;
        }

        $timestamp = strtotime($header);

        return $timestamp === false ? null : max(0, $timestamp - time());
    }
}
