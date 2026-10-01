<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Contracts;

use Juksgraphic\BladeTranslator\Exceptions\TranslationFileException;

/**
 * Derives a stable, filesystem-safe page slug from a template path.
 */
interface SlugResolverInterface
{
    /**
     * @param string $path Path to a template file, e.g. "resources/views/student/home/index.blade.php".
     * @return string Slug such as "student/home/index".
     *
     * @throws TranslationFileException When the path is unsafe (e.g. contains "..").
     */
    public function resolve(string $path): string;
}
