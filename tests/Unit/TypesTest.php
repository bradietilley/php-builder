<?php

use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Builder\Types\PhpGeneric;
use BradieTilley\Builder\Types\PhpIntersectionType;
use BradieTilley\Builder\Types\PhpNamedType;
use BradieTilley\Builder\Types\PhpUnionType;

test('named type exports native and phpdoc forms', function () {
    $type = new PhpNamedType('string');

    expect($type->toPhp())->toBe('string')
        ->and($type->toPhpDoc())->toBe('string')
        ->and($type->needsPhpDoc())->toBeFalse();
});

test('nullable named type uses question mark for native', function () {
    $type = new PhpNamedType('string', nullable: true);

    expect($type->toPhp())->toBe('?string')
        ->and($type->toPhpDoc())->toBe('string|null');
});

test('array generic exports array natively and generics in phpdoc', function () {
    $type = PhpGeneric::array(value: 'string');

    expect($type->toPhp())->toBe('array')
        ->and($type->toPhpDoc())->toBe('array<string>')
        ->and($type->needsPhpDoc())->toBeTrue();
});

test('array generic supports key and value', function () {
    $type = PhpGeneric::array(value: 'string', key: 'int', nullable: true);

    expect($type->toPhp())->toBe('?array')
        ->and($type->toPhpDoc())->toBe('array<int, string>|null');
});

test('array generic supports array-key', function () {
    $type = PhpGeneric::array(key: 'array-key', value: 'string');

    expect($type->toPhp())->toBe('array')
        ->and($type->toPhpDoc())->toBe('array<array-key, string>');
});

test('list generic uses array natively and list in phpdoc', function () {
    $type = PhpGeneric::list(value: 'string', nullable: true);

    expect($type->toPhp())->toBe('?array')
        ->and($type->toPhpDoc())->toBe('list<string>|null');
});

test('iterable generic exports iterable natively', function () {
    $type = PhpGeneric::iterable(value: 'int');

    expect($type->toPhp())->toBe('iterable')
        ->and($type->toPhpDoc())->toBe('iterable<int>');
});

test('iterable generic supports key and value', function () {
    $type = PhpGeneric::iterable(value: 'string', key: 'int');

    expect($type->toPhp())->toBe('iterable')
        ->and($type->toPhpDoc())->toBe('iterable<int, string>');
});

test('for factory exports class generics', function () {
    $type = PhpGeneric::for('Illuminate\\Support\\Collection', key: 'string', value: 'int');

    expect($type->toPhp())->toBe('Illuminate\\Support\\Collection')
        ->and($type->toPhpDoc())->toBe('Illuminate\\Support\\Collection<string, int>')
        ->and($type->needsPhpDoc())->toBeTrue();
});

test('generic type resolves imports on base name and nested types', function () {
    $imports = new ImportBag(namespace: 'App\\Models');
    $type = PhpGeneric::for(
        'Illuminate\\Support\\Collection',
        key: 'string',
        value: 'App\\Models\\Post',
        nullable: true,
    )->withResolvedImports($imports);

    expect($type->toPhp())->toBe('?Collection')
        ->and($type->toPhpDoc())->toBe('Collection<string, Post>|null')
        ->and($imports->toUseLines())->toBe(['use Illuminate\\Support\\Collection;']);
});

test('union and intersection types export correctly', function () {
    $union = new PhpUnionType(['string', 'int']);
    $intersection = new PhpIntersectionType(['Countable', 'Iterator']);

    expect($union->toPhp())->toBe('string|int')
        ->and($intersection->toPhp())->toBe('Countable&Iterator');
});
