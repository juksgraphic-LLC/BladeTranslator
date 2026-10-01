<?php

declare(strict_types=1);

namespace Juksgraphic\BladeTranslator\Support;

use Juksgraphic\BladeTranslator\Contracts\PromptBuilderInterface;
use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;

/**
 * Default prompts, written for Laravel Blade and EFTEC BladeOne alike.
 */
final class PromptBuilder implements PromptBuilderInterface
{
    public function __construct(
        private readonly TranslatorConfig $config,
    ) {}

    public function systemForExtraction(): string
    {
        $prompt = <<<'PROMPT'
You are a translatable content extractor for Blade templates (Laravel Blade
or EFTEC BladeOne). You are given the content of a .blade.php file. You must
produce ONLY a flat JSON object, key -> text, containing all the text meant
to be translated in this file. Nothing else: no markdown, no comment, no
text before or after the JSON.

KEY NAMING RULES:
1. Format "{section}.{element}", where {section} is the closest HTML or
   Blade section id (e.g. "hero", "faq", "context"). If no section is
   identifiable, use "page.{element}".
2. For an element repeated in a loop (@foreach, .map, PHP array):
   "{section}.items.{slug}.{field}", where {slug} is a stable identifier
   derived from the item's content (e.g. "mobilize", "free_consultation"),
   NEVER a numeric index. Two successive runs on an unchanged file must
   produce the same keys.
3. For text with two variants depending on a condition (@if/@else with
   DIFFERENT contents): two separate keys, suffixed by the meaning of
   each branch (e.g. "welcome.company_title" / "welcome.individual_title"),
   never by "if_true"/"if_false" or "1"/"0".
4. For simple conditional display (@if without @else, which shows or
   hides an entire block): extract the text normally, without a special
   suffix: the condition only concerns visibility, not the content.
5. If a list of existing keys is provided, reuse the exact same key whenever
   its text is still present in the file. Create new keys only for new texts.

INLINE VARIABLES AND MARKUP:
6. When a visible text embeds a simple variable ({{ $user->name }},
   {{ $count }}), extract the whole sentence and replace the variable with a
   named placeholder written like [[PLACEHOLDER_SAMPLE]]. Example:
   "Hello {{ $user->name }}!" becomes "Hello [[PLACEHOLDER_SAMPLE]]!".
   Name the placeholder after the last segment of the variable
   ($user->name -> name). The same variable always gets the same placeholder.
   Never use any other placeholder syntax.
7. Keep simple inline tags (<strong>, <em>, <b>, <i>, <br>) inside the value.
   For links or any tag with dynamic attributes (href="{{ route(...) }}"),
   extract only the visible text, as its own key.

WHAT TO EXTRACT:
- All text read by the user: titles, paragraphs, button labels,
  placeholders, alt/title/aria-label attributes, labels in an Alpine
  x-data (e.g. "label" of a menu item).

WHAT TO NEVER EXTRACT:
- PHP/Blade values computed at render time: {{ $loop->index }},
  {{ $loop->iteration }}, date(), or any value that changes dynamically
  without being fixed text. (Static text around an inline variable is
  extracted, see rule 6.)
- Data coming from a database model (e.g. $partner->name, $post->title):
  this data has its own translation system, never treat it as static page text.
- Alpine.js state or bindings: x-data (state values like "open: 0"),
  :class, x-show, x-bind, @click: never extract content from these.
- Technical attributes: src, technical href, class, id, data-*.
- Icons / SVG paths.
- Translation calls that already exist: __('...'), trans(), trans_choice(),
  @lang('...'), and BladeOne's _e(), @_e(), _ef(): never re-extract them,
  they are already handled elsewhere.
- A text made only of a brand name, third-party platform or organization
  acronym (e.g. "YouTube", "PayPal"): no key. Inside a sentence, leave it as is.

OUTPUT FORMAT: a single flat JSON object. All values are strings.
No nesting, no arrays, no comments.
PROMPT;

        $prompt = strtr($prompt, [
            '[[PLACEHOLDER_SAMPLE]]' => $this->config->placeholderStyle->format('name'),
        ]);

        if ($this->config->doNotTranslate !== []) {
            $prompt .= "\n\nTERMS ALWAYS KEPT AS IS (never create a key for a text made only of one of them):\n"
                . implode(', ', $this->config->doNotTranslate);
        }

        return $prompt;
    }

    public function userForExtraction(string $slug, string $content, array $existingKeys = []): string
    {
        $prompt = "Page name: {$slug}\n\n";

        if ($existingKeys !== []) {
            $prompt .= "Existing keys for this page (reuse them when their text is still present):\n"
                . implode("\n", $existingKeys) . "\n\n";
        }

        return $prompt . "Blade file content:\n\n{$content}";
    }

    public function systemForLocalization(string $targetLocale): string
    {
        $prompt = <<<'PROMPT'
You are a professional translator. You are given a flat JSON object,
key -> text. Translate ONLY the values into [[LANGUAGE]].

STRICT RULES:
1. NEVER change the keys, nor their order. Every input key must appear
   exactly once in the output.
2. Do not translate brand names, third-party platform names or acronyms:
   leave them as is.
3. Preserve every placeholder exactly as is, character for character
   (e.g. :name, {name}, %s, %1$s, {{ $x }}). You may move a placeholder
   within the sentence if the target grammar requires it.
4. Preserve HTML tags and their attributes exactly; translate only the
   text between them.
5. Adapt tone and idiomatic expressions, do not translate word for word.
6. Respond ONLY with the translated JSON object, same structure, no
   text before/after, no markdown.
PROMPT;

        $prompt = strtr($prompt, [
            '[[LANGUAGE]]' => $this->config->labelFor($targetLocale),
        ]);

        if ($this->config->doNotTranslate !== []) {
            $prompt .= "\n7. Never translate these terms: " . implode(', ', $this->config->doNotTranslate) . '.';
        }

        return $prompt;
    }

    public function userForLocalization(array $source, string $targetLocale): string
    {
        $json = json_encode(
            $source,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        return "Target language: {$this->config->labelFor($targetLocale)}\n\nJSON to translate:\n\n{$json}";
    }
}
