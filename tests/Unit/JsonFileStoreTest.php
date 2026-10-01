<?php

use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\Exceptions\TranslationFileException;
use Juksgraphic\BladeTranslator\Storage\JsonFileStore;

beforeEach(function () {
    
    $this->tempDir = sys_get_temp_dir() . '/blade_translator_test_' . uniqid();
    mkdir($this->tempDir, 0777, true);

    $this->config = new TranslatorConfig(
        sourceLocale: 'fr',
        targetLocales: ['en', 'es'],
        translationsPath: $this->tempDir,
    );

    $this->store = new JsonFileStore($this->config, sortKeys: true);
});

afterEach(function () {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $todo = $fileinfo->isDir() ? 'rmdir' : 'unlink';
        $todo($fileinfo->getRealPath());
    }

    rmdir($this->tempDir);
});

it('correctly writes and reads a translation JSON file', function () {
    $data = ['welcome' => 'Bienvenue', 'hello' => 'Bonjour'];

    $this->store->write('home/index', 'fr', $data);

    expect($this->store->exists('home/index', 'fr'))->toBeTrue();
    expect($this->store->read('home/index', 'fr'))->toBe([
        'hello' => 'Bonjour',
        'welcome' => 'Bienvenue',
    ]);
});

it('merges new keys without overwriting existing ones by default', function () {
    $this->store->write('pages/about', 'fr', ['title' => 'À propos']);

    $merged = $this->store->merge('pages/about', 'fr', [
        'title' => 'Old Title',
        'description' => 'Our story',
    ]);

    expect($merged)->toBe([
        'title' => 'À propos',
        'description' => 'Our story',
    ]);
});

it('overwrites existing keys when overwrite parameter is true', function () {
    $this->store->write('pages/about', 'fr', ['title' => 'Old Title']);

    $merged = $this->store->merge('pages/about', 'fr', ['title' => 'New Title'], overwrite: true);

    expect($merged['title'])->toBe('New Title');
});

it('prevents path traversal attempts in slugs', function (string $unsafeSlug) {
    expect(fn() => $this->store->read($unsafeSlug, 'fr'))
        ->toThrow(TranslationFileException::class);
})->with([
    '../secret',
    'dir/../../etc/passwd',
    'slug:with:colon',
    '/absolute/path',
]);
