<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator;

use Closure;
use Juksgraphic\BladeTranslator\Contracts\AiProviderInterface;
use Juksgraphic\BladeTranslator\Contracts\PromptBuilderInterface;
use Juksgraphic\BladeTranslator\Contracts\ResponseParserInterface;
use Juksgraphic\BladeTranslator\Contracts\SlugResolverInterface;
use Juksgraphic\BladeTranslator\Contracts\StoreInterface;
use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\Exceptions\InvalidAiResponseException;
use Juksgraphic\BladeTranslator\Exceptions\InvalidConfigurationException;
use Juksgraphic\BladeTranslator\Exceptions\ResponseTruncatedException;
use Juksgraphic\BladeTranslator\Exceptions\TranslationFileException;
use Juksgraphic\BladeTranslator\Storage\JsonFileStore;
use Juksgraphic\BladeTranslator\Support\PromptBuilder;
use Juksgraphic\BladeTranslator\Support\ResponseParser;
use Juksgraphic\BladeTranslator\Support\SlugResolver;
use Juksgraphic\BladeTranslator\Support\TranslationValidator;

/**
 * Orchestrates page translation:
 *  - generate(): extracts translatable texts from a Blade file into the source locale;
 *  - translate(): fills the missing keys of target locales, in validated batches.
 *
 * Depends only on contracts, so every collaborator can be replaced.
 */
final class PageTranslator
{
    /** How many times the AI is asked to fix an invalid answer, after the first attempt. */
    private const int MAX_CORRECTIONS = 1;

    private readonly PromptBuilderInterface $prompts;

    private readonly ResponseParserInterface $parser;

    private readonly SlugResolverInterface $slugs;

    private readonly TranslationValidator $validator;

    /**
     * @param AiProviderInterface $provider AI used to extract and translate.
     * @param StoreInterface $store Where translation maps are persisted.
     * @param TranslatorConfig $config Locales, batch size, glossary...
     * @param PromptBuilderInterface|null $prompts Defaults to PromptBuilder.
     * @param ResponseParserInterface|null $parser Defaults to ResponseParser.
     * @param SlugResolverInterface|null $slugs Defaults to SlugResolver.
     * @param TranslationValidator|null $validator Defaults to TranslationValidator.
     */
    public function __construct(
        private readonly AiProviderInterface $provider,
        private readonly StoreInterface $store,
        private readonly TranslatorConfig $config,
        ?PromptBuilderInterface $prompts = null,
        ?ResponseParserInterface $parser = null,
        ?SlugResolverInterface $slugs = null,
        ?TranslationValidator $validator = null,
    ) {
        $this->prompts   = $prompts ?? new PromptBuilder($config);
        $this->parser    = $parser ?? new ResponseParser();
        $this->slugs     = $slugs ?? new SlugResolver($config);
        $this->validator = $validator ?? new TranslationValidator();
    }

    /**
     * Convenience factory using the JSON file store and every default collaborator.
     */
    public static function make(AiProviderInterface $provider, TranslatorConfig $config): self
    {
        return new self($provider, new JsonFileStore($config), $config);
    }

    /**
     * Slug a Blade file will be stored under.
     *
     * @throws TranslationFileException
     */
    public function slugFor(string $bladePath): string
    {
        return $this->slugs->resolve($bladePath);
    }

    /**
     * Extract translatable texts from a Blade file and merge them into the source locale.
     * Existing keys (and manual corrections) are never overwritten.
     *
     * The file is sent in one request: a template too large for the model's output
     * limit raises ResponseTruncatedException.
     *
     * @return array<string, string> Merged source map, including previously existing keys.
     *
     * @throws TranslationFileException
     * @throws InvalidAiResponseException
     * @throws \Juksgraphic\BladeTranslator\Exceptions\ProviderException
     */
    public function generate(string $bladePath): array
    {
        $slug    = $this->slugs->resolve($bladePath);
        $source  = $this->config->sourceLocale;
        $content = $this->compact($this->readBlade($bladePath));
        $current = $this->store->readIfExists($slug, $source);

        if (trim($content) === '') {
            return $current;
        }

        $extracted = $this->requestMap(
            $this->prompts->systemForExtraction(),
            $this->prompts->userForExtraction($slug, $content, array_map('strval', array_keys($current))),
        );

        return $this->store->merge($slug, $source, $extracted);
    }

    /**
     * Translate only the keys missing from each target locale, preserving existing translations.
     * Each batch is persisted as soon as it is validated, so a failure never loses finished work.
     *
     * @param string $slug Page slug to translate.
     * @param string|array<int, string>|null $locales Target locale(s); null = all configured targets.
     * @return array<string, array<string, string>> Resulting maps keyed by locale.
     *
     * @throws InvalidConfigurationException
     * @throws TranslationFileException
     * @throws InvalidAiResponseException
     * @throws \Juksgraphic\BladeTranslator\Exceptions\ProviderException
     */
    public function translate(string $slug, string|array|null $locales = null): array
    {
        $targets = array_values(array_unique($locales === null ? $this->config->targetLocales : (array) $locales));

        foreach ($targets as $target) {
            if (!TranslatorConfig::isValidLocale($target)) {
                throw InvalidConfigurationException::invalidLocale($target);
            }
        }

        $source     = $this->store->locate($slug);
        $sourceData = $this->store->read($slug, $source->locale);
        $results    = [];

        foreach ($targets as $target) {
            $results[$target] = $target === $source->locale
                ? $sourceData
                : $this->translateInto($slug, $sourceData, $target);
        }

        return $results;
    }

    /**
     * @param array<string, string> $sourceData
     * @return array<string, string>
     */
    private function translateInto(string $slug, array $sourceData, string $target): array
    {
        $current = $this->store->readIfExists($slug, $target);
        $missing = array_diff_key($sourceData, $current);

        foreach (array_chunk($missing, $this->config->batchSize, true) as $batch) {
            $translated = $this->translateBatch($batch, $target);
            $current    = $this->store->merge($slug, $target, $translated);
        }

        return $current;
    }

    /**
     * Translate one batch; if the answer is truncated, split the batch in two and retry each half.
     *
     * @param array<string, string> $batch
     * @return array<string, string>
     *
     * @throws ResponseTruncatedException When even a single key cannot fit.
     */
    private function translateBatch(array $batch, string $target): array
    {
        try {
            return $this->requestMap(
                $this->prompts->systemForLocalization($target),
                $this->prompts->userForLocalization($batch, $target),
                fn(array $translated) => $this->validator->validate($batch, $translated),
            );
        } catch (ResponseTruncatedException $e) {
            if (count($batch) <= 1) {
                throw $e;
            }

            $translated = [];

            foreach (array_chunk($batch, (int) ceil(count($batch) / 2), true) as $half) {
                $translated = array_replace($translated, $this->translateBatch($half, $target));
            }

            return $translated;
        }
    }

    /**
     * Ask the AI for a flat map, parse it and optionally validate it.
     * An invalid answer is sent back once to the AI together with the reason it was rejected.
     *
     * @param Closure(array<string, string>): void|null $validate Throws InvalidAiResponseException when the map is unacceptable.
     * @return array<string, string>
     *
     * @throws ResponseTruncatedException
     * @throws InvalidAiResponseException
     * @throws \Juksgraphic\BladeTranslator\Exceptions\ProviderException
     */
    private function requestMap(string $system, string $user, ?Closure $validate = null): array
    {
        $feedback = null;

        for ($attempt = 0;; $attempt++) {
            $prompt = $feedback === null
                ? $user
                : "{$user}\n\nYour previous answer was rejected: {$feedback}\nReturn ONLY the corrected JSON object, nothing else.";

            $response = $this->provider->ask($system, $prompt);

            if ($response->isTruncated()) {
                throw ResponseTruncatedException::create();
            }

            try {

                $map = $this->parser->parse($response->content);

                if ($validate !== null) {
                    $validate($map);
                }

                return $map;

            } catch (InvalidAiResponseException $e) {
                
                if ($attempt >= self::MAX_CORRECTIONS) {
                    throw $e;
                }

                $feedback = $e->getMessage();
            }
        }
    }

    /**
     * Reads blade file
     * @param string $path
     * @throws TranslationFileException
     */
    private function readBlade(string $path): string
    {
        if (!is_file($path)) {
            throw TranslationFileException::notFound($path);
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw TranslationFileException::unreadable($path);
        }

        return $content;
    }

    /**
     * Drop what never contains translatable text (Blade comments, <style>, <svg>) to save tokens.
     * <script> blocks are kept: they may hold user-facing strings.
     */
    private function compact(string $content): string
    {
        $patterns = [
            '/\{\{--.*?--\}\}/s',
            '#<style\b.*?</style>#is',
            '#<svg\b.*?</svg>#is',
        ];

        return preg_replace($patterns, '', $content) ?? $content;
    }
}
