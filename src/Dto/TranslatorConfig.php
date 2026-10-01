<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Dto;

use Juksgraphic\BladeTranslator\Exceptions\InvalidConfigurationException;

/**
 * Immutable, validated configuration shared by the translator components.
 *
 * Provider-level settings (temperature, timeout, retries, max tokens)
 * belong to the providers, not here.
 */
final readonly class TranslatorConfig
{
    /**
     * Human-readable names sent to the AI instead of raw locale codes.
     */
    public const array DEFAULT_LOCALE_LABELS = [
        'en' => 'English',
        'fr' => 'French',
        'es' => 'Spanish',
        'pt' => 'Portuguese',
        'de' => 'German',
        'it' => 'Italian',
        'ht' => 'Haitian Creole (Kreyòl ayisyen)',
    ];

    /** Folder (without trailing slash) where locale JSON files are stored. */
    public string $translationsPath;

    /** Base folder of the Blade views, used to build stable slugs. Null = no base. */
    public ?string $viewsPath;

    public string $sourceLocale;

    /** @var list<string> Locales to translate into (never contains the source locale). */
    public array $targetLocales;

    /** @var array<string, string> locale => label, defaults merged with user overrides. */
    public array $localeLabels;

    /** @var list<string> Terms the AI must leave untouched (brands, acronyms...). */
    public array $doNotTranslate;

    /** Maximum number of keys sent to the AI in a single request. */
    public int $batchSize;

    /** Placeholder syntax the AI must emit at extraction time. */
    public PlaceholderStyle $placeholderStyle;

    /**
     * @param string $translationsPath Root folder where locale files are stored.
     * @param string $sourceLocale Locale of the extracted source texts.
     * @param array<int, string> $targetLocales Locales to translate into.
     * @param array<string, string> $localeLabels Extra or overriding locale labels.
     * @param array<int, string> $doNotTranslate Glossary of terms to keep as is.
     * @param int $batchSize Max keys per AI request.
     * @param string|null $viewsPath Base folder of the Blade views.
     * @param PlaceholderStyle $placeholderStyle Placeholder syntax emitted at extraction (all styles are accepted when reading).
     *
     * @throws InvalidConfigurationException
     */
    public function __construct(
        string $translationsPath,
        string $sourceLocale = 'en',
        array $targetLocales = ['fr', 'es', 'ht'],
        array $localeLabels = [],
        array $doNotTranslate = [],
        int $batchSize = 40,
        ?string $viewsPath = null,
        PlaceholderStyle $placeholderStyle = PlaceholderStyle::Colon,
    ) {
        $translationsPath = rtrim(trim($translationsPath), '/\\');

        if ($translationsPath === '') {
            throw InvalidConfigurationException::emptyPath('translationsPath');
        }

        if ($batchSize < 1) {
            throw InvalidConfigurationException::invalidValue('batchSize', 'must be at least 1');
        }

        self::assertValidLocale($sourceLocale);

        foreach ($targetLocales as $locale) {
            self::assertValidLocale($locale);
        }

        $targets = array_values(array_unique(
            array_filter($targetLocales, static fn (string $l): bool => $l !== $sourceLocale)
        ));

        if ($targets === []) {
            throw InvalidConfigurationException::noTargetLocales();
        }

        foreach (array_keys($localeLabels) as $locale) {
            self::assertValidLocale((string) $locale);
        }

        $this->translationsPath = $translationsPath;
        $this->viewsPath = $viewsPath === null ? null : rtrim($viewsPath, '/\\');
        $this->sourceLocale = $sourceLocale;
        $this->targetLocales = $targets;
        $this->localeLabels = array_merge(self::DEFAULT_LOCALE_LABELS, $localeLabels);
        $this->doNotTranslate = array_values(array_unique(array_filter(
            array_map('trim', $doNotTranslate),
            static fn (string $term): bool => $term !== ''
        )));
        $this->batchSize = $batchSize;
        $this->placeholderStyle = $placeholderStyle;
    }

    /**
     * Label sent to the AI for a locale, falling back to the raw code.
     */
    public function labelFor(string $locale): string
    {
        return $this->localeLabels[$locale] ?? $locale;
    }

    /**
     * All locales handled by the package, source first.
     *
     * @return list<string>
     */
    public function allLocales(): array
    {
        return [$this->sourceLocale, ...$this->targetLocales];
    }

    /**
     * Locale codes end up in file paths, so the format is strict
     * (this also blocks path traversal such as "../").
     *
     * @throws InvalidConfigurationException
     */
    private static function assertValidLocale(string $locale): void
    {
        if (preg_match('/^[a-z]{2,3}(?:-[A-Za-z0-9]{2,8})*$/', $locale) !== 1) {
            throw InvalidConfigurationException::invalidLocale($locale);
        }
    }

    /**
     * Whether a string is a safe locale code (shared with the storage layer).
     */
    public static function isValidLocale(string $locale): bool
    {
        return preg_match('/^[a-z]{2,3}(?:-[A-Za-z0-9]{2,8})*$/', $locale) === 1;
    }
}
