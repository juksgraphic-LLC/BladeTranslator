<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Exceptions;

use Throwable;

/**
 * Raised on any problem reading, writing or locating translation/source files.
 */
class TranslationFileException extends TranslatorException
{
    /**
     * File not found
     * @param string $path
     * @return TranslationFileException
     */
    public static function notFound(string $path): self
    {
        return new self("File not found: {$path}");
    }

    /**
     * File cannot be read
     * @param string $path
     * @return TranslationFileException
     */
    public static function unreadable(string $path): self
    {
        return new self("Cannot read file: {$path}");
    }

    /**
     * File is not a valid json
     * @param string $path
     * @param mixed $previous
     * @return TranslationFileException
     */
    public static function invalidJson(string $path, ?Throwable $previous = null): self
    {
        return new self("Invalid JSON content in: {$path}", 0, $previous);
    }

    /**
     * Cannot create directory
     * @param string $dir
     * @return TranslationFileException
     */
    public static function cannotCreateDirectory(string $dir): self
    {
        return new self("Cannot create directory: {$dir}");
    }

    /**
     * Cannot write file
     * @param string $path
     * @param mixed $previous
     * @return TranslationFileException
     */
    public static function cannotWrite(string $path, ?Throwable $previous = null): self
    {
        return new self("Cannot write file: {$path}", 0, $previous);
    }

    /**
     * No source file not found
     * @param list<string> $locales
     */
    public static function sourceNotFound(string $slug, array $locales): self
    {
        return new self("No source translation found for '{$slug}' in locales: " . implode(', ', $locales));
    }

    /**
     * Unsafe path
     * @param string $path
     * @return TranslationFileException
     */
    public static function unsafePath(string $path): self
    {
        return new self("Unsafe path rejected: {$path}");
    }
}
