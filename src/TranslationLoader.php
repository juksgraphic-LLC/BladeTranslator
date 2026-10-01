<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator;

use Closure;
use Juksgraphic\BladeTranslator\Contracts\StoreInterface;
use Juksgraphic\BladeTranslator\Exceptions\TranslationFileException;
use Stringable;

/**
 * Runtime side of the package: resolves translated texts for a page,
 * with a locale fallback chain, placeholder replacement and in-memory caching.
 *
 * A broken translation file never breaks page rendering: the error is reported
 * to the optional $onError callback (e.g. a logger) and the next locale is tried.
 */
final class TranslationLoader
{
    /** One pass over both syntaxes: {name} and :name (see PlaceholderValidator for the matching rules). */
    private const string PLACEHOLDER = '/\{([A-Za-z_]\w*)\}|(?<![\w:\/]):([A-Za-z_]\w*)/';

    /** @var array<string, array<string, string>> */
    private array $cache = [];

    /**
     * @param StoreInterface $store Where translation maps are read from.
     * @param string $locale Active locale.
     * @param array<int, string> $fallbackLocales Locales tried in order when a key is missing, e.g. ['fr', 'en'].
     * @param Closure(TranslationFileException): void|null $onError Called when a translation file cannot be read.
     */
    public function __construct(
        private readonly StoreInterface $store,
        private string $locale,
        private readonly array $fallbackLocales = ['en'],
        private readonly ?Closure $onError = null,
    ) {}

    /**
     * Change the active locale (e.g. per request).
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * Translated text for a page key, or the raw key when no locale has it.
     *
     * @param string $slug Page slug, e.g. "student/home/index" or "layout/navbar".
     * @param string $key Translation key, e.g. "hero.title".
     * @param array<string, scalar|Stringable|null> $replace Values for {name} / :name placeholders.
     * @param string|null $locale Locale override; null = active locale.
     */
    public function get(string $slug, string $key, array $replace = [], ?string $locale = null): string
    {
        foreach ($this->chain($locale) as $candidate) {
            $data = $this->load($slug, $candidate);

            if (array_key_exists($key, $data)) {
                return $this->replace($data[$key], $replace);
            }
        }

        return $key;
    }

    /**
     * Whether any locale of the chain defines the key.
     */
    public function has(string $slug, string $key, ?string $locale = null): bool
    {
        foreach ($this->chain($locale) as $candidate) {
            if (array_key_exists($key, $this->load($slug, $candidate))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whole map of a page, with fallback locales filling the gaps (e.g. to export it to JavaScript).
     *
     * @return array<string, string>
     */
    public function all(string $slug, ?string $locale = null): array
    {
        $merged = [];

        foreach (array_reverse($this->chain($locale)) as $candidate) {
            $merged = array_replace($merged, $this->load($slug, $candidate));
        }

        return $merged;
    }

    /**
     * Forget cached maps (e.g. after translations were regenerated in a long-running process).
     */
    public function flush(): void
    {
        $this->cache = [];
    }

    /**
     * @return list<string> Requested locale first, then fallbacks, without duplicates.
     */
    private function chain(?string $locale): array
    {
        return array_values(array_unique([$locale ?? $this->locale, ...$this->fallbackLocales]));
    }

    /**
     * @return array<string, string>
     */
    private function load(string $slug, string $locale): array
    {
        $cacheKey = "{$locale}:{$slug}";

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            return $this->cache[$cacheKey] = $this->store->readIfExists($slug, $locale);
        } catch (TranslationFileException $e) {
            if ($this->onError !== null) {
                ($this->onError)($e);
            }

            return $this->cache[$cacheKey] = [];
        }
    }

    /**
     * Replace placeholders in a single pass, so inserted values are never re-interpreted.
     * Unknown placeholders are left untouched.
     *
     * @param array<string, scalar|Stringable|null> $replace
     */
    private function replace(string $text, array $replace): string
    {
        if ($replace === []) {
            return $text;
        }

        return preg_replace_callback(
            self::PLACEHOLDER,
            static function (array $match) use ($replace): string {
                $name = ($match[1] ?? '') !== '' ? $match[1] : ($match[2] ?? '');

                return array_key_exists($name, $replace) ? (string) $replace[$name] : $match[0];
            },
            $text,
        ) ?? $text;
    }
}
