# BladeTranslator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/juksgraphic/bladetranslator.svg?style=flat-square)](https://packagist.org/packages/juksgraphic/bladetranslator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/juksgraphic/bladetranslator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/juksgraphic/bladetranslator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/juksgraphic/bladetranslator.svg?style=flat-square)](https://packagist.org/packages/juksgraphic/bladetranslator)
[![License](https://img.shields.io/packagist/l/juksgraphic/bladetranslator.svg?style=flat-square)](LICENSE.md)

**BladeTranslator** is an AI-powered PHP/Laravel package designed to automate the extraction, translation, and management of language files directly from your Blade views.

It automatically sanitizes unneeded HTML/SVG markup, extracts translatable text, manages batch splitting during AI response truncation, self-corrects invalid AI responses, and securely stores translations in JSON format.

---

## Features

- 🧹 **Smart Blade Extraction**: Removes comments, `<svg>` tags, and unnecessary HTML noise before sending payloads to the AI.
- 🤖 **AI-Driven Translation**: Flexible integration with feedback loops for self-correction when keys or placeholders are mismatched.
- 📦 **Dynamic Batch Splitting**: Automatically splits batches into smaller chunks if the AI response gets truncated (`finish_reason: length`).
- 🔒 **Secure JSON Storage**: Prevents path traversal attempts and supports optional alphabetical key sorting.
- 🌐 **Robust Translation Loader**: Full support for dynamic placeholders (`:name` and `{name}`) and intelligent locale fallback chains.

---

## Requirements

- **PHP**: `^8.4`
- **Composer**

---

## Installation

You can install the package via Composer:

```bash
composer require juksgraphic/bladetranslator
```

---

## Quick Start

### 1. Configuration (`TranslatorConfig`)

Initialize the package configuration with your project settings:

```php
use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;

$config = new TranslatorConfig(
    sourceLocale: 'fr',
    targetLocales: ['en', 'es'],
    translationsPath: resource_path('lang'),
    viewsPath: resource_path('views'),
    batchSize: 10,
);
```

### 2. Extract and Translate Blade Views (`PageTranslator`)

Use `PageTranslator` alongside your custom AI provider (implementing `ProviderInterface`):

```php
use Juksgraphic\BladeTranslator\PageTranslator;
use Juksgraphic\BladeTranslator\Providers\CerebrasProvider; // OpenAI, Cerebras, etc.

$provider   = new CerebrasProvider("your-cerebras-api-key" , "your-model-use");

$translator = PageTranslator::make($provider, $config)

// 1. Extract translatable keys from a Blade view
$extractedKeys = $translator->generate(resource_path('views/pages/about.blade.php'));

// 2. Translate the domain/slug into configured target locales
$translations = $translator->translate('pages/about', ['en', 'es']);
```

### 3. Load Translations (`TranslationLoader`)

Retrieve translated strings seamlessly with variable interpolation and fallback handling:

```php
use Juksgraphic\BladeTranslator\TranslationLoader;

$loader = new TranslationLoader(
    store: $store,
    locale: 'en',
    fallbackLocales: ['fr'],
    onError: function (\Throwable $e) {
        logger()->error('Translation loading error: ' . $e->getMessage());
    }
);

// Returns: "Hello John" (interpolated from "Hello :user" or "Hello {user}")
echo $loader->get('pages/about', 'welcome', ['user' => 'John']);
```

---

## Testing

Run the test suite via Pest PHP:

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

---

## Contributing

Please see [CONTRIBUTING.md](.github/CONTRIBUTING.md) for contribution guidelines.

---

## Security Vulnerabilities

If you discover any security-related issues, please email [contact@juksgraphic.com](mailto:contact@juksgraphic.com) instead of using the public issue tracker.

---

## Credits

- [Kingsley Guillaume](https://github.com/juksgraphic-LLC) — Lead Developer & Creator
- [All Contributors](../../contributors)

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.