<?php

use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\Support\ImportBag;

test('import returns basename when available', function () {
    $bag = new ImportBag('App\\Models');

    expect($bag->import('Illuminate\\Support\\Collection'))->toBe('Collection')
        ->and($bag->toUseLines())->toBe([
            'use Illuminate\\Support\\Collection;',
        ]);
});

test('import skips use for same-namespace siblings', function () {
    $bag = new ImportBag('App\\Models');

    expect($bag->import('App\\Models\\Post'))->toBe('Post')
        ->and($bag->toUseLines())->toBe([]);
});

test('import aliases with last plus second-last on clash', function () {
    $bag = new ImportBag('App\\Models');
    $bag->import('App\\Models\\Concerns\\HasSlug');

    expect($bag->import('Example\\For\\DuplicateTrait\\HasSlug'))->toBe('HasSlugDuplicateTrait')
        ->and($bag->toUseLines())->toBe([
            'use App\\Models\\Concerns\\HasSlug;',
            'use Example\\For\\DuplicateTrait\\HasSlug as HasSlugDuplicateTrait;',
        ]);
});

test('import escalates parent segments until unique', function () {
    $bag = new ImportBag();
    $bag->import('A\\HasSlug');
    $bag->import('B\\DuplicateTrait\\HasSlug');

    expect($bag->import('C\\Other\\DuplicateTrait\\HasSlug'))->toBe('HasSlugDuplicateTraitOther')
        ->and($bag->import('A\\HasSlug'))->toBe('HasSlug');
});

test('import passes through builtins and short names', function () {
    $bag = new ImportBag();

    expect($bag->import('string'))->toBe('string')
        ->and($bag->import('Model'))->toBe('Model')
        ->and($bag->toUseLines())->toBe([]);
});

test('php class import is available and used in generated file', function () {
    $class = new PhpClass(
        namespace: 'App\\Models',
        name: 'Post',
        extends: 'App\\Models\\Model',
    );

    $alias = $class->import('Illuminate\\Database\\Eloquent\\Model');

    expect($alias)->toBe('ModelEloquent')
        ->and($class->toPhp())->toContain('use Illuminate\\Database\\Eloquent\\Model as ModelEloquent;')
        ->and($class->toPhp())->toContain('class Post extends Model');
});

test('import accepts an explicit manual alias', function () {
    $bag = new ImportBag('App\\Models');
    $bag->import('App\\Models\\Concerns\\HasSlug');

    expect($bag->import('Example\\For\\DuplicateTrait\\HasSlug', 'SlugTrait'))->toBe('SlugTrait')
        ->and($bag->toUseLines())->toBe([
            'use App\\Models\\Concerns\\HasSlug;',
            'use Example\\For\\DuplicateTrait\\HasSlug as SlugTrait;',
        ]);
});

test('php class import accepts an explicit manual alias', function () {
    $class = new PhpClass(
        namespace: 'App\\Models',
        name: 'Post',
        extends: 'App\\Models\\Model',
    );

    $alias = $class->import('Illuminate\\Database\\Eloquent\\Model', 'EloquentModel');

    expect($alias)->toBe('EloquentModel')
        ->and($class->toPhp())->toContain('use Illuminate\\Database\\Eloquent\\Model as EloquentModel;')
        ->and($class->toPhp())->toContain('class Post extends Model');
});
