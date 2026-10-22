<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Support;

use FilesystemIterator;
use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\Exceptions\TranslationFileException;
use Juksgraphic\BladeTranslator\Storage\JsonFileStore;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Discovers the available translation slugs and allows manual editing.
 * Scanned structure: {translationsPath}/{locale}/pages/{slug}.json
 * Slug examples: "emails/contacts/confirm", "global/pages/blog/index"
 */
final class SlugCatalog
{
    /** Segments ignored when generating a human-readable label. */
    private const array DEFAULT_HIDDEN_SEGMENTS = ['global', 'pages', 'partials'];

    /** Slug prefix => display group name. */
    private const array DEFAULT_GROUPS = [
        'emails/'          => 'Emails',
        'global/partials/' => 'Shared components',
        'global/pages/'    => 'Pages',
    ];

    /**
     * In-memory cache of slugs per locale (avoids rescanning the disk).
     *
     * @var array<string, list<string>>
     */
    private array $cache = [];

    /**
     * @param TranslatorConfig           $config         Locales and translations path.
     * @param JsonFileStore              $store          Where translation maps are read and written.
     * @param array<string, string>      $labels         Manual labels: slug => readable name.
     * @param array<string, string>|null $groups         Prefix => group name (null = built-in defaults).
     * @param list<string>|null          $hiddenSegments Segments hidden in generated labels (null = built-in defaults).
     */
    public function __construct(
        private readonly TranslatorConfig $config,
        private readonly JsonFileStore $store,
        private readonly array $labels = [],
        private readonly ?array $groups = null,
        private readonly ?array $hiddenSegments = null,
    ) {
    }

    /**
     * Slugs available for a locale (cached).
     *
     * @return list<string>
     *
     * @throws TranslationFileException If the locale is not valid.
     */
    public function slugsFor(string $locale): array
    {
        if (! TranslatorConfig::isValidLocale($locale)) {
            throw TranslationFileException::unsafePath($locale);
        }

        return $this->cache[$locale] ??= $this->scan($locale);
    }

    /**
     * Clears the cache. Call it when files were created or deleted
     * outside of this catalog (e.g. after a generate() call).
     */
    public function refresh(): void
    {
        $this->cache = [];
    }

    /**
     * Available slugs, grouped by locale.
     *
     * @return array<string, list<string>>
     */
    public function all(): array
    {
        $result = [];

        foreach ($this->config->allLocales() as $locale) {
            $result[$locale] = $this->slugsFor($locale);
        }

        return $result;
    }

    /**
     * Union of all known slugs, across every locale.
     *
     * @return list<string>
     */
    public function allSlugs(): array
    {
        $union = array_unique(array_merge(...array_values($this->all())));
        sort($union, SORT_STRING);

        return array_values($union);
    }

    /**
     * Slugs present in the source locale but missing from a target locale.
     *
     * @return list<string>
     */
    public function missingFor(string $locale): array
    {
        return array_values(array_diff(
            $this->slugsFor($this->config->sourceLocale),
            $this->slugsFor($locale),
        ));
    }

    /**
     * Summary table for an admin interface:
     * for each slug, which locales exist and how many keys are missing.
     *
     * @return array<string, array<string, array{exists: bool, keys: int, missing: int}>>
     */
    public function overview(): array
    {
        $source = $this->config->sourceLocale;
        $overview = [];

        foreach ($this->slugsFor($source) as $slug) {
            $sourceKeys = array_keys($this->store->read($slug, $source));

            foreach ($this->config->allLocales() as $locale) {
                if (! $this->store->exists($slug, $locale)) {
                    $overview[$slug][$locale] = [
                        'exists'  => false,
                        'keys'    => 0,
                        'missing' => count($sourceKeys),
                    ];
                    continue;
                }

                $keys = array_keys($this->store->read($slug, $locale));

                $overview[$slug][$locale] = [
                    'exists'  => true,
                    'keys'    => count($keys),
                    'missing' => count(array_diff($sourceKeys, $keys)),
                ];
            }
        }

        return $overview;
    }

    /**
     * Reads a slug so it can be displayed in an edit form.
     * Returns an empty array if the file does not exist.
     *
     * @return array<string, string>
     */
    public function load(string $slug, string $locale): array
    {
        return $this->store->readIfExists($slug, $locale);
    }

    /**
     * Saves manually edited values (overwrites the provided keys,
     * keeps the others). Only keys already present in the source locale are accepted;
     * non-string values are ignored.
     *
     * @param array<string, mixed> $values
     * @return array<string, string> Resulting map.
     */
    public function save(string $slug, string $locale, array $values): array
    {
        $allowed = array_keys($this->store->readIfExists($slug, $this->config->sourceLocale));
        $clean = [];

        foreach ($values as $key => $value) {
            $key = (string) $key;

            if (! in_array($key, $allowed, true) || ! is_string($value)) {
                continue;
            }

            $clean[$key] = $value;
        }

        $result = $this->store->merge($slug, $locale, $clean, overwrite: true);
        unset($this->cache[$locale]); // the file may have just been created

        return $result;
    }

    /**
     * Fully replaces the content of a file (for a raw JSON editor).
     *
     * @param array<string, string> $data
     */
    public function replace(string $slug, string $locale, array $data): void
    {
        $this->store->write($slug, $locale, $data);
        unset($this->cache[$locale]);
    }

    /**
     * Human-readable page name for a slug.
     * Unknown slug: generated from the path ("global/pages/faq" => "Faq").
     */
    public function label(string $slug): string
    {
        if (isset($this->labels[$slug])) {
            return $this->labels[$slug];
        }

        $hidden = $this->hiddenSegments ?? self::DEFAULT_HIDDEN_SEGMENTS;

        $segments = array_filter(
            explode('/', $slug),
            static fn(string $s): bool => ! in_array($s, $hidden, true),
        );

        $readable = array_map(
            static fn(string $s): string => ucfirst(str_replace(['-', '_'], ' ', $s)),
            $segments,
        );

        return $readable === [] ? $slug : implode(': ', $readable);
    }

    /**
     * Display group of a slug (used to group the list in the admin).
     * Falls back to "Other" when no prefix matches.
     */
    public function group(string $slug): string
    {
        foreach ($this->groups ?? self::DEFAULT_GROUPS as $prefix => $name) {
            if (str_starts_with($slug, $prefix)) {
                return $name;
            }
        }

        return 'Other';
    }

    /**
     * Source locale slugs with their label and group.
     *
     * @return list<array{slug: string, label: string, group: string}>
     */
    public function labeled(): array
    {
        return array_map(
            fn(string $slug): array => [
                'slug'  => $slug,
                'label' => $this->label($slug),
                'group' => $this->group($slug),
            ],
            $this->slugsFor($this->config->sourceLocale),
        );
    }

    /** Source locale (the one holding the original texts). */
    public function sourceLocale(): string
    {
        return $this->config->sourceLocale;
    }

    /**
     * Every managed locale (source + targets).
     *
     * @return list<string>
     */
    public function locales(): array
    {
        return array_values($this->config->allLocales());
    }

    /** Does the slug exist in the source locale? */
    public function has(string $slug): bool
    {
        return in_array($slug, $this->slugsFor($this->sourceLocale()), true);
    }

    /**
     * Actual disk scan for a locale (the locale is already validated).
     *
     * @return list<string>
     */
    private function scan(string $locale): array
    {
        $root = rtrim(str_replace('\\', '/', $this->config->translationsPath), '/')
            . "/{$locale}/pages";

        if (! is_dir($root)) {
            return [];
        }

        $slugs = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || strtolower($file->getExtension()) !== 'json') {
                continue;
            }

            $path = str_replace('\\', '/', $file->getPathname());
            $relative = substr($path, strlen($root) + 1);
            $slugs[] = substr($relative, 0, -5); // strip ".json"
        }

        sort($slugs, SORT_STRING);

        return $slugs;
    }
}