<?php

use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\PhpTemplate;
use BradieTilley\Builder\Types\PhpCallableType;

test('callable type exports native callable and signed phpdoc', function () {
    $type = new PhpCallableType(
        parameters: ['int', 'string'],
        return: 'bool',
    );

    expect($type->toPhp())->toBe('callable')
        ->and($type->toPhpDoc())->toBe('callable(int, string): bool')
        ->and($type->needsPhpDoc())->toBeTrue();
});

test('class templates export as @template tags', function () {
    $class = new PhpClass(
        namespace: 'App',
        name: 'Collection',
        description: 'A generic collection',
        templates: [
            new PhpTemplate(name: 'TKey', of: 'array-key'),
            'TValue',
        ],
        strictTypes: false,
    );

    expect($class->toPhp())->toContain(<<<'PHP'
/**
 * A generic collection
 *
 * @template TKey of array-key
 * @template TValue
 */
class Collection
PHP);
});

test('method templates export in method phpdoc', function () {
    $method = new PhpMethod(
        name: 'map',
        templates: [new PhpTemplate(name: 'TReturn')],
        return: 'array',
        lines: ['return [];'],
    );

    expect($method->toPhp())->toContain('@template TReturn');
});
