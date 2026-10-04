<?php

use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\PageTranslator;
use Juksgraphic\BladeTranslator\Providers\CerebrasProvider;
use Juksgraphic\BladeTranslator\Storage\JsonFileStore;
use Juksgraphic\BladeTranslator\TranslationLoader;

include_once("vendor/autoload.php");

$config = new TranslatorConfig(
    translationsPath: __DIR__ . '/lang',
    sourceLocale: 'fr',
    targetLocales: ['en', 'es', 'ht'],
    viewsPath: __DIR__ . '/views',
);

$provider = new CerebrasProvider(
    "your-api-key",
    "your-model-use"
);

$translator = PageTranslator::make($provider, $config);

$store  = new JsonFileStore($config);
$loader = new TranslationLoader($store , "en");

