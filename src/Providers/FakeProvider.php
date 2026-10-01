<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Providers;

use Juksgraphic\BladeTranslator\Contracts\AiProviderInterface;
use Juksgraphic\BladeTranslator\Dto\AiResponse;
use Juksgraphic\BladeTranslator\Exceptions\ProviderException;
use Throwable;

/**
 * Test double that replays queued answers and records every prompt it receives.
 *
 * Queue a string (wrapped in an AiResponse with finishReason "stop"), a full
 * AiResponse, or a Throwable to simulate a failure.
 */
final class FakeProvider implements AiProviderInterface
{
    /** @var list<string|AiResponse|Throwable> */
    private array $queue = [];

    /** @var list<array{system: string, user: string}> */
    private array $calls = [];

    /**
     * @param array<int, string|AiResponse|Throwable> $responses Answers returned in order.
     */
    public function __construct(array $responses = [])
    {
        foreach ($responses as $response) {
            $this->push($response);
        }
    }

    public function push(string|AiResponse|Throwable $response): self
    {
        $this->queue[] = $response;

        return $this;
    }

    public function ask(string $system, string $user): AiResponse
    {
        $this->calls[] = ['system' => $system, 'user' => $user];

        if ($this->queue === []) {
            throw ProviderException::requestFailed('FakeProvider has no queued response left.');
        }

        $next = array_shift($this->queue);

        if ($next instanceof Throwable) {
            throw $next;
        }

        return $next instanceof AiResponse ? $next : new AiResponse($next, 'stop');
    }

    /**
     * @return list<array{system: string, user: string}>
     */
    public function calls(): array
    {
        return $this->calls;
    }

    public function callCount(): int
    {
        return count($this->calls);
    }
}
