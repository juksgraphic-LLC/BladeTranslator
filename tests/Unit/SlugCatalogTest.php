<?php

use Juksgraphic\BladeTranslator\Dto\TranslatorConfig;
use Juksgraphic\BladeTranslator\Exceptions\TranslationFileException;
use Juksgraphic\BladeTranslator\Storage\JsonFileStore;
use Juksgraphic\BladeTranslator\Support\SlugCatalog; // adapte au namespace final

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . '/blade_translator_catalog_test_' . uniqid();
    mkdir($this->tempDir, 0777, true);

    $this->config = new TranslatorConfig(
        sourceLocale: 'fr',
        targetLocales: ['en', 'es'],
        translationsPath: $this->tempDir,
    );

    $this->store = new JsonFileStore($this->config, sortKeys: true);
    $this->catalog = new SlugCatalog($this->config, $this->store);
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

/*
|--------------------------------------------------------------------------
| slugsFor() / cache
|--------------------------------------------------------------------------
*/

it('returns an empty list when the locale has no translation folder', function () {
    expect($this->catalog->slugsFor('fr'))->toBe([]);
});

it('lists slugs of a locale, nested ones included, sorted', function () {
    $this->store->write('home/index', 'fr', ['a' => 'A']);
    $this->store->write('emails/contacts/confirm', 'fr', ['a' => 'A']);
    $this->store->write('about', 'fr', ['a' => 'A']);

    expect($this->catalog->slugsFor('fr'))->toBe([
        'about',
        'emails/contacts/confirm',
        'home/index',
    ]);
});

it('ignores non-json files when scanning', function () {
    $this->store->write('home/index', 'fr', ['a' => 'A']);
    file_put_contents($this->tempDir . '/fr/pages/notes.txt', 'ignore me');

    expect($this->catalog->slugsFor('fr'))->toBe(['home/index']);
});

it('rejects invalid locales', function (string $locale) {
    expect(fn () => $this->catalog->slugsFor($locale))
        ->toThrow(TranslationFileException::class);
})->with([
    '../etc',
    'fr/../en',
    '/absolute',
]);

it('serves slugs from cache until refresh() is called', function () {
    expect($this->catalog->slugsFor('en'))->toBe([]);

    // File created behind the catalog's back
    $this->store->write('home/index', 'en', ['a' => 'A']);

    expect($this->catalog->slugsFor('en'))->toBe([]);

    $this->catalog->refresh();

    expect($this->catalog->slugsFor('en'))->toBe(['home/index']);
});

/*
|--------------------------------------------------------------------------
| all() / allSlugs() / missingFor() / has()
|--------------------------------------------------------------------------
*/

it('groups slugs by locale', function () {
    $this->store->write('home', 'fr', ['a' => 'A']);
    $this->store->write('home', 'en', ['a' => 'A']);

    expect($this->catalog->all())->toBe([
        'fr' => ['home'],
        'en' => ['home'],
        'es' => [],
    ]);
});

it('returns the sorted union of all slugs without duplicates', function () {
    $this->store->write('home', 'fr', ['a' => 'A']);
    $this->store->write('home', 'en', ['a' => 'A']);
    $this->store->write('about', 'en', ['a' => 'A']);
    $this->store->write('blog', 'es', ['a' => 'A']);

    expect($this->catalog->allSlugs())->toBe(['about', 'blog', 'home']);
});

it('returns an empty union when nothing exists', function () {
    expect($this->catalog->allSlugs())->toBe([]);
});

it('lists slugs present in the source locale but missing from a target', function () {
    $this->store->write('home', 'fr', ['a' => 'A']);
    $this->store->write('about', 'fr', ['a' => 'A']);
    $this->store->write('home', 'en', ['a' => 'A']);

    expect($this->catalog->missingFor('en'))->toBe(['about']);
    expect($this->catalog->missingFor('es'))->toBe(['about', 'home']);
});

it('tells whether a slug exists in the source locale', function () {
    $this->store->write('home', 'fr', ['a' => 'A']);
    $this->store->write('only-en', 'en', ['a' => 'A']);

    expect($this->catalog->has('home'))->toBeTrue();
    expect($this->catalog->has('only-en'))->toBeFalse();
    expect($this->catalog->has('unknown'))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| overview()
|--------------------------------------------------------------------------
*/

it('builds an overview with existing locales and missing key counts', function () {
    $this->store->write('home', 'fr', ['a' => 'A', 'b' => 'B', 'c' => 'C']);
    $this->store->write('home', 'en', ['a' => 'A']);
    // "es" does not exist

    expect($this->catalog->overview())->toBe([
        'home' => [
            'fr' => ['exists' => true,  'keys' => 3, 'missing' => 0],
            'en' => ['exists' => true,  'keys' => 1, 'missing' => 2],
            'es' => ['exists' => false, 'keys' => 0, 'missing' => 3],
        ],
    ]);
});

it('returns an empty overview when the source locale has no slug', function () {
    expect($this->catalog->overview())->toBe([]);
});

/*
|--------------------------------------------------------------------------
| load() / save() / replace()
|--------------------------------------------------------------------------
*/

it('loads a slug, or an empty array when the file does not exist', function () {
    $this->store->write('home', 'fr', ['a' => 'A']);

    expect($this->catalog->load('home', 'fr'))->toBe(['a' => 'A']);
    expect($this->catalog->load('home', 'en'))->toBe([]);
});

it('saves edited values, overwriting provided keys and keeping the others', function () {
    $this->store->write('home', 'fr', ['title' => 'Titre', 'intro' => 'Intro']);
    $this->store->write('home', 'en', ['title' => 'Old', 'intro' => 'Intro EN']);

    $result = $this->catalog->save('home', 'en', ['title' => 'New title']);

    expect($result)->toEqual(['title' => 'New title', 'intro' => 'Intro EN']);
    expect($this->store->read('home', 'en'))->toEqual(['title' => 'New title', 'intro' => 'Intro EN']);
});

it('ignores unknown keys and non-string values on save', function () {
    $this->store->write('home', 'fr', ['title' => 'Titre', 'intro' => 'Intro']);

    $result = $this->catalog->save('home', 'en', [
        'title'   => 'Title',
        'intro'   => ['not', 'a', 'string'],
        'unknown' => 'Should be dropped',
    ]);

    expect($result)->toEqual(['title' => 'Title']);
});

it('invalidates the cache of the locale after save()', function () {
    $this->store->write('home', 'fr', ['title' => 'Titre']);

    expect($this->catalog->slugsFor('en'))->toBe([]);

    $this->catalog->save('home', 'en', ['title' => 'Title']);

    expect($this->catalog->slugsFor('en'))->toBe(['home']);
});

it('replaces the whole content of a file and invalidates the cache', function () {
    $this->store->write('home', 'fr', ['a' => 'A']);

    expect($this->catalog->slugsFor('en'))->toBe([]);

    $this->catalog->replace('home', 'en', ['x' => 'X', 'y' => 'Y']);

    expect($this->store->read('home', 'en'))->toEqual(['x' => 'X', 'y' => 'Y']);
    expect($this->catalog->slugsFor('en'))->toBe(['home']);

    $this->catalog->replace('home', 'en', ['z' => 'Z']);

    expect($this->store->read('home', 'en'))->toEqual(['z' => 'Z']);
});

/*
|--------------------------------------------------------------------------
| label() / group() / labeled()
|--------------------------------------------------------------------------
*/

it('prefers a manual label when one is defined', function () {
    $catalog = new SlugCatalog($this->config, $this->store, ['global/pages/faq' => 'Questions fréquentes']);

    expect($catalog->label('global/pages/faq'))->toBe('Questions fréquentes');
});

it('generates a readable label from the slug', function (string $slug, string $expected) {
    expect($this->catalog->label($slug))->toBe($expected);
})->with([
    ['global/pages/faq', 'Faq'],
    ['global/pages/blog/index', 'Blog: Index'],
    ['emails/contacts/confirm', 'Emails: Contacts: Confirm'],
    ['global/partials/main-menu', 'Main menu'],
    ['user_profile', 'User profile'],
]);

it('falls back to the raw slug when every segment is hidden', function () {
    expect($this->catalog->label('global/pages'))->toBe('global/pages');
});

it('uses custom hidden segments for labels', function () {
    $catalog = new SlugCatalog($this->config, $this->store, hiddenSegments: ['app']);

    expect($catalog->label('app/settings'))->toBe('Settings');
    expect($catalog->label('global/pages/faq'))->toBe('Global: Pages: Faq');
});

it('resolves the default display groups', function (string $slug, string $expected) {
    expect($this->catalog->group($slug))->toBe($expected);
})->with([
    ['emails/contacts/confirm', 'Emails'],
    ['global/partials/footer', 'Shared components'],
    ['global/pages/home', 'Pages'],
    ['something/else', 'Other'],
]);

it('uses custom groups when provided', function () {
    $catalog = new SlugCatalog($this->config, $this->store, groups: ['admin/' => 'Back office']);

    expect($catalog->group('admin/users'))->toBe('Back office');
    expect($catalog->group('emails/welcome'))->toBe('Other');
});

it('returns the source slugs with their label and group', function () {
    $this->store->write('emails/welcome', 'fr', ['a' => 'A']);
    $this->store->write('global/pages/faq', 'fr', ['a' => 'A']);

    expect($this->catalog->labeled())->toBe([
        ['slug' => 'emails/welcome',  'label' => 'Emails: Welcome', 'group' => 'Emails'],
        ['slug' => 'global/pages/faq', 'label' => 'Faq',            'group' => 'Pages'],
    ]);
});

/*
|--------------------------------------------------------------------------
| Locales
|--------------------------------------------------------------------------
*/

it('exposes the source locale and every managed locale', function () {
    expect($this->catalog->sourceLocale())->toBe('fr');
    expect($this->catalog->locales())->toBe(['fr', 'en', 'es']);
});