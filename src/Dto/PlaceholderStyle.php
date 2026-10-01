<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Dto;

/**
 * Placeholder syntax the AI must emit when extracting texts that contain variables.
 *
 * Reading and validation always accept every style (:name, {name}, %s, {{ $x }});
 * this only decides which one is *produced*, so a page stays consistent.
 */
enum PlaceholderStyle: string
{
    /** Laravel style: "Hello :name!" */
    case Colon = 'colon';

    /** ICU / generic style: "Hello {name}!" */
    case Brace = 'brace';

    /**
     * Render a placeholder name in this style.
     */
    public function format(string $name): string
    {
        return match ($this) {
            self::Colon => ':' . $name,
            self::Brace => '{' . $name . '}',
        };
    }
}