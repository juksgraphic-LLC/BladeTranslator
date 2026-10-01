<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Contracts;

use Juksgraphic\BladeTranslator\Dto\TranslationSource;
use Juksgraphic\BladeTranslator\Exceptions\TranslationFileException;

/**
 * Persistence of flat translation maps (key => text), addressed by page slug and locale.
 *
 * Callers never deal with paths, so the storage can be JSON files, a database, etc.
 */
interface StoreInterface
{
    /**
     * Find the first known locale that has a translation map for the slug.
     *
     * @throws TranslationFileException When no locale has one.
     */
    public function locate(string $slug): TranslationSource;

    /**
     * Whether a translation map exists for the slug in this locale.
     */
    public function exists(string $slug, string $locale): bool;

    /**
     * Read a translation map. Fails if it does not exist or is corrupted.
     *
     * @return array<string, string>
     *
     * @throws TranslationFileException
     */
    public function read(string $slug, string $locale): array;

    /**
     * Read a translation map, returning an empty array when it does not exist.
     * A corrupted map must still raise an exception, never be silently ignored.
     *
     * @return array<string, string>
     *
     * @throws TranslationFileException
     */
    public function readIfExists(string $slug, string $locale): array;

    /**
     * Replace the whole translation map.
     *
     * @param array<string, string> $data
     *
     * @throws TranslationFileException
     */
    public function write(string $slug, string $locale, array $data): void;

    /**
     * Merge incoming entries into the stored map and persist the result.
     *
     * By default existing keys win, which protects manual corrections.
     * Pass $overwrite = true to let incoming values replace existing ones.
     *
     * @param array<string, string> $incoming
     * @return array<string, string> The map that was persisted.
     *
     * @throws TranslationFileException
     */
    public function merge(string $slug, string $locale, array $incoming, bool $overwrite = false): array;
}
