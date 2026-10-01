<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Contracts;

/**
 * Builds the system/user prompts for the two AI tasks:
 * extracting texts from a template, and localizing a key/text map.
 */
interface PromptBuilderInterface
{
    /**
     * Instructions defining extraction and key-naming rules.
     */
    public function systemForExtraction(): string;

    /**
     * @param string $slug Page slug, used as context.
     * @param string $content Raw template content to analyze.
     * @param array<int, string> $existingKeys Keys already stored for this page, so the AI reuses them.
     */
    public function userForExtraction(string $slug, string $content, array $existingKeys = []): string;

    /**
     * Instructions defining translation rules for a flat JSON map.
     *
     * @param string $targetLocale Target locale code (e.g. "ht"); the builder resolves the readable label.
     */
    public function systemForLocalization(string $targetLocale): string;

    /**
     * @param array<string, string> $source Flat key => text map to translate.
     * @param string $targetLocale Target locale code.
     */
    public function userForLocalization(array $source, string $targetLocale): string;
}
