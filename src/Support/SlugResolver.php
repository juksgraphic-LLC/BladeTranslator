<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Support;

use Juksgraphic\BladeTranslator\Contracts\SlugResolverInterface;
use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\Exceptions\TranslationFileException;

/**
 * Builds a page slug relative to the views folder.
 *
 * Unlike the previous version, a "pages" folder is NOT stripped, so
 * "pages/home.blade.php" and "home.blade.php" no longer collide.
 */
final class SlugResolver implements SlugResolverInterface
{
    public function __construct(
        private readonly TranslatorConfig $config,
    ) {
    }

    public function resolve(string $path): string
    {
        $normalized = str_replace('\\', '/', trim($path));

        if ($normalized === '' || str_contains($normalized, "\0")) {
            throw TranslationFileException::unsafePath($path);
        }

        $relative = $this->relativeToViews($normalized, $path);
        $relative = preg_replace('#\.(blade\.php|php)$#i', '', $relative) ?? $relative;

        $segments = [];

        foreach (explode('/', $relative) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..' || str_contains($segment, ':')) {
                throw TranslationFileException::unsafePath($path);
            }

            $segments[] = $segment;
        }

        if ($segments === []) {
            throw TranslationFileException::unsafePath($path);
        }

        return implode('/', $segments);
    }

    /**
     * Strip the configured views folder, or fall back to the first "/views/" segment.
     * An absolute path that matches neither is rejected rather than guessed.
     */
    private function relativeToViews(string $normalized, string $original): string
    {
        $base = $this->config->viewsPath;

        if ($base !== null) {
            $base = rtrim(str_replace('\\', '/', $base), '/') . '/';

            if (str_starts_with($normalized, $base)) {
                return substr($normalized, strlen($base));
            }
        }

        $position = strpos($normalized, '/views/');

        if ($position !== false) {
            return substr($normalized, $position + 7);
        }

        if (str_starts_with($normalized, '/') || preg_match('#^[A-Za-z]:/#', $normalized) === 1) {
            throw TranslationFileException::unsafePath($original);
        }

        return $normalized;
    }
}
