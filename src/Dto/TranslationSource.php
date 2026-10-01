<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Dto;

/**
 * Locale and path where a page's source translation file was found.
 */
final readonly class TranslationSource
{
    /**
     * @param string $locale Locale in which the source file was found.
     * @param string $path Absolute path to the source JSON file.
     */
    public function __construct(
        public string $locale,
        public string $path,
    ) {
    }
}
