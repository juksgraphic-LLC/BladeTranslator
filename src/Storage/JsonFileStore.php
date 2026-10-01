<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Storage;

use JsonException;
use Juksgraphic\BladeTranslator\Contracts\StoreInterface;
use Juksgraphic\BladeTranslator\Dto\TranslationSource;
use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\Exceptions\TranslationFileException;

/**
 * Stores translation maps as JSON files:
 *
 * {translationsPath}/{locale}/pages/{slug}.json
 *
 * Writes are atomic (temporary file + rename), so a crash can never leave a
 * half-written file. Slugs and locales are validated to prevent path traversal.
 *
 * Limitation: merge() is read-modify-write without a cross-process lock
 * (last write wins). Do not run two translation jobs on the same page at once.
 */
final class JsonFileStore implements StoreInterface
{
    /**
     * @param TranslatorConfig $config Provides the root folder and the locales searched by locate().
     * @param bool $sortKeys Sort keys alphabetically on write (cleaner Git diffs, loses page order).
     */
    public function __construct(
        private readonly TranslatorConfig $config,
        private readonly bool $sortKeys = false,
    ) {
    }

    /**
     * Summary of locate
     * @param string $slug
     * @return TranslationSource
     */
    public function locate(string $slug): TranslationSource
    {
        $locales = $this->config->allLocales();

        foreach ($locales as $locale) {
            $path = $this->pathFor($slug, $locale);

            if (is_file($path)) {
                return new TranslationSource($locale, $path);
            }
        }

        throw TranslationFileException::sourceNotFound($slug, $locales);
    }

    /**
     * Verify if a slug exists
     * @param string $slug
     * @param string $locale
     * @return bool
     */
    public function exists(string $slug, string $locale): bool
    {
        return is_file($this->pathFor($slug, $locale));
    }

    /**
     * Read a value derived form a slug in a json file
     * @param string $slug
     * @param string $locale
     * @return string[]
     */
    public function read(string $slug, string $locale): array
    {
        $path = $this->pathFor($slug, $locale);

        if (! is_file($path)) {
            throw TranslationFileException::notFound($path);
        }

        $raw = file_get_contents($path);

        if ($raw === false) {
            throw TranslationFileException::unreadable($path);
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw TranslationFileException::invalidJson($path, $e);
        }

        if (! is_array($data)) {
            throw TranslationFileException::invalidJson($path);
        }

        $map = [];

        foreach ($data as $key => $value) {

            if (! is_string($value)) {
                throw TranslationFileException::invalidJson($path);
            }

            $map[(string) $key] = $value;
        }

        return $map;
    }

    /**
     * Read if values exists
     * @param string $slug
     * @param string $locale
     * @return string[]
     */
    public function readIfExists(string $slug, string $locale): array
    {
        return $this->exists($slug, $locale) ? $this->read($slug, $locale) : [];
    }

    /**
     * Write in file
     * @param string $slug
     * @param string $locale
     * @param array $data
     * @return void
     */
    public function write(string $slug, string $locale, array $data): void
    {
        $path = $this->pathFor($slug, $locale);
        $dir = dirname($path);

        if (
            ! is_dir($dir) &&
            ! mkdir($dir, 0755, true) &&
            ! is_dir($dir)
        ) {
            throw TranslationFileException::cannotCreateDirectory($dir);
        }

        if ($this->sortKeys) {
            ksort($data, SORT_STRING);
        }

        try {
            $json = json_encode(
                $data,
                JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_FORCE_OBJECT
                    | JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            throw TranslationFileException::cannotWrite($path, $e);
        }

        $this->writeAtomically($path, $json . "\n");
    }

    /**
     * Summary of merge
     * @param string $slug
     * @param string $locale
     * @param array $incoming
     * @param bool $overwrite
     * @return array
     */
    public function merge(string $slug, string $locale, array $incoming, bool $overwrite = false): array
    {
        $existing = $this->readIfExists($slug, $locale);

        // "+" and array_replace() keep keys untouched (array_merge would renumber
        // numeric keys) and keep existing keys at their current position.
        $merged = $overwrite
            ? array_replace($existing, $incoming)
            : $existing + $incoming;

        $this->write($slug, $locale, $merged);

        return $merged;
    }

    /**
     * Absolute path of the JSON file for a page and locale.
     * @param string $slug
     * @param string $locale
     * @throws TranslationFileException When slug or locale is unsafe.
     */
    public function pathFor(string $slug, string $locale): string
    {
        $this->assertSafe($slug, $locale);

        return "{$this->config->translationsPath}/{$locale}/pages/{$slug}.json";
    }

    /**
     * @throws TranslationFileException
     */
    private function assertSafe(string $slug, string $locale): void
    {
        if (! TranslatorConfig::isValidLocale($locale)) {
            throw TranslationFileException::unsafePath($locale);
        }

        if (
            $slug === ''
            || str_contains($slug, "\0")
            || str_contains($slug, '\\')
            || str_starts_with($slug, '/')
        ) {
            throw TranslationFileException::unsafePath($slug);
        }

        foreach (explode('/', $slug) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || str_contains($segment, ':')) {
                throw TranslationFileException::unsafePath($slug);
            }
        }
    }

    /**
     * Write to a temporary file in the same directory, then rename over the target.
     *
     * @throws TranslationFileException
     */
    private function writeAtomically(string $path, string $contents): void
    {
        $temporary = tempnam(dirname($path), '.tmp_');

        if ($temporary === false) {
            throw TranslationFileException::cannotWrite($path);
        }

        if (file_put_contents($temporary, $contents, LOCK_EX) === false) {
            $this->discard($temporary);

            throw TranslationFileException::cannotWrite($path);
        }

        // tempnam() creates files with 0600; translations should be readable by the web server.
        chmod($temporary, 0644);

        if (! rename($temporary, $path)) {
            $this->discard($temporary);

            throw TranslationFileException::cannotWrite($path);
        }
    }

    /**
     * Summary of discard
     * @param string $file
     * @return void
     */
    private function discard(string $file): void
    {
        if (is_file($file)) {
            unlink($file);
        }
    }
}
