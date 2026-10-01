<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Exceptions;

/**
 * Raised when the package is configured with invalid values.
 */
class InvalidConfigurationException extends TranslatorException
{
    /**
     * Invalid local
     * @param string $locale
     * @return InvalidConfigurationException
     */
    public static function invalidLocale(string $locale): self
    {
        return new self("Invalid locale code '{$locale}'. Expected something like 'en', 'fr' or 'pt-BR'.");
    }
   
    /**
     * No targets locale
     * @return InvalidConfigurationException
     */
    public static function noTargetLocales(): self
    {
        return new self('At least one target locale different from the source locale is required.');
    }

    /**
     * Empty Path
     * @param string $name
     * @return InvalidConfigurationException
     */
    public static function emptyPath(string $name): self
    {
        return new self("Configuration value '{$name}' must not be empty.");
    }

    /**
     * Invalid value
     * @param string $name
     * @param string $expectation
     * @return InvalidConfigurationException
     */
    public static function invalidValue(string $name, string $expectation): self
    {
        return new self("Configuration value '{$name}' is invalid: {$expectation}.");
    }
}