<?php

use Juksgraphic\BladeTranslator\Dto\AiResponse;
use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\PageTranslator;
use Juksgraphic\BladeTranslator\Providers\FakeProvider;
use Juksgraphic\BladeTranslator\Storage\JsonFileStore;

beforeEach(function () {
    
    $this->tempDir = sys_get_temp_dir() . '/blade_translator_test_' . uniqid();
    mkdir($this->tempDir, 0777, true);

    $this->config = new TranslatorConfig(
        sourceLocale: 'fr',
        targetLocales: ['en'],
        translationsPath: $this->tempDir,
        viewsPath: $this->tempDir, 
        batchSize: 10,
    );

    $this->store = new JsonFileStore($this->config);
    $this->provider = new FakeProvider();
    $this->translator = new PageTranslator($this->provider, $this->store, $this->config);
});

afterEach(function () {
    if (is_dir($this->tempDir)) {
        exec('rm -rf ' . escapeshellarg($this->tempDir));
    }
});

it('extracts translatable text from a Blade file and strips unneeded HTML markup', function () {
    $bladePath = $this->tempDir . '/home.blade.php';
    file_put_contents($bladePath, '<h1>{{-- Comment --}}Hello {name}</h1><svg>...</svg><script>console.log("ok")</script>');

    $this->provider->push(json_encode(['hero.title' => 'Hello {name}']));

    $result = $this->translator->generate($bladePath);

    expect($result)->toBe(['hero.title' => 'Hello {name}']);
    expect($this->provider->callCount())->toBe(1);

    $lastCall = $this->provider->calls()[0];
    expect($lastCall['user'])->not->toContain('Comment');
    expect($lastCall['user'])->not->toContain('<svg>');
    expect($lastCall['user'])->toContain('<script>');
});

it('automatically splits a batch in two when the AI response is truncated', function () {
    $this->store->write('dashboard', 'fr', [
        'key1' => 'Text 1',
        'key2' => 'Text 2',
        'key3' => 'Text 3',
        'key4' => 'Text 4',
    ]);

    // Premier essai (lot complet de 4 clés) -> Réponse tronquée
    $this->provider->push(new AiResponse('{"key1": "Text 1"', finishReason: 'length'));

    // Retours des deux sous-lots de 2 clés chacun
    $this->provider->push(json_encode(['key1' => 'Text 1', 'key2' => 'Text 2']));
    $this->provider->push(json_encode(['key3' => 'Text 3', 'key4' => 'Text 4']));

    $result = $this->translator->translate('dashboard', ['en']);

    expect($result['en'])->toBe([
        'key1' => 'Text 1',
        'key2' => 'Text 2',
        'key3' => 'Text 3',
        'key4' => 'Text 4',
    ]);
    expect($this->provider->callCount())->toBe(3);
});

it('sends feedback to the AI for self-correction upon receiving an invalid response', function () {
    $this->store->write('profile', 'fr', ['greeting' => 'Bonjour :name']);

    $this->provider->push(json_encode(['greeting' => 'Hello']));
    $this->provider->push(json_encode(['greeting' => 'Hello :name']));

    $result = $this->translator->translate('profile', ['en']);

    expect($result['en']['greeting'])->toBe('Hello :name');
    expect($this->provider->callCount())->toBe(2);
    expect($this->provider->calls()[1]['user'])->toContain('Your previous answer was rejected');
});