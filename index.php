<?php

use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\PageTranslator;
use Juksgraphic\BladeTranslator\Providers\CerebrasProvider;

include_once("vendor/autoload.php");

$config = new TranslatorConfig(
    translationsPath: __DIR__ . '/lang',
    sourceLocale: 'fr',
    targetLocales: ['en', 'es', 'ht'],
    doNotTranslate: ['YouTube', 'PayPal'],
    viewsPath: __DIR__ . '/views',
);

$provider = new CerebrasProvider(
    "your-cerebras-api-key",
    "gpt-oss-120b"
);

$translator = PageTranslator::make($provider, $config);

$page = $translator->generate(__DIR__ . '/views/test.blade.php');

dd($page);