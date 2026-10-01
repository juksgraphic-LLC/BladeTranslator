<?php

use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\Storage\JsonFileStore;
use Juksgraphic\BladeTranslator\TranslationLoader;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . '/blade_translator_test_' . uniqid();
    mkdir($this->tempDir, 0777, true);

    $this->config = new TranslatorConfig(
        sourceLocale: 'fr',
        targetLocales: ['en', 'ht'],
        translationsPath: $this->tempDir,
    );

    $this->store = new JsonFileStore($this->config);

    // Prepare fixture files
    $this->store->write('nav', 'fr', ['title' => 'Accueil', 'welcome' => 'Bienvenue :user']);
    $this->store->write('nav', 'en', ['welcome' => 'Welcome :user']); // 'title' missing in 'en'

    $this->loader = new TranslationLoader(
        store: $this->store,
        locale: 'en',
        fallbackLocales: ['fr'],
    );
});

afterEach(function () {
    if (is_dir($this->tempDir)) {
        exec('rm -rf ' . escapeshellarg($this->tempDir));
    }
});

it('correctly interpolates :name and {name} placeholders', function () {
    $text = $this->loader->get('nav', 'welcome', ['user' => 'Kingsley']);

    expect($text)->toBe('Welcome Kingsley');
});

it('falls back to fallback locales when a key is missing in the current locale', function () {
    // Key 'title' missing in 'en', falls back to 'fr'
    $title = $this->loader->get('nav', 'title');

    expect($title)->toBe('Accueil');
});

it('handles corrupted JSON files gracefully without breaking and triggers the onError callback', function () {
    $badPath = $this->tempDir . '/ht/pages/broken.json';
    mkdir(dirname($badPath), 0755, true);
    file_put_contents($badPath, '{ invalid json ...');

    $errorLogged = false;

    $loader = new TranslationLoader(
        store: $this->store,
        locale: 'ht',
        fallbackLocales: ['fr'],
        onError: function () use (&$errorLogged) {
            $errorLogged = true;
        }
    );

    $result = $loader->get('broken', 'any.key');

    expect($result)->toBe('any.key');
    expect($errorLogged)->toBeTrue();
});
