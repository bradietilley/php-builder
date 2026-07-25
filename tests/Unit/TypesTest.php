<?php

use BradieTilley\Builder\Types\PhpArrayType;
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

test('array type exports array natively and generics in phpdoc', function () {
    $type = new PhpArrayType(value: 'string');

    expect($type->toPhp())->toBe('array')
        ->and($type->toPhpDoc())->toBe('array<string>')
        ->and($type->needsPhpDoc())->toBeTrue();
});

test('array type supports key and value generics', function () {
    $type = new PhpArrayType(value: 'string', key: 'int', nullable: true);

    expect($type->toPhp())->toBe('?array')
        ->and($type->toPhpDoc())->toBe('array<int, string>|null');
});

test('union and intersection types export correctly', function () {
    $union = new PhpUnionType(['string', 'int']);
    $intersection = new PhpIntersectionType(['Countable', 'Iterator']);

    expect($union->toPhp())->toBe('string|int')
        ->and($intersection->toPhp())->toBe('Countable&Iterator');
});
