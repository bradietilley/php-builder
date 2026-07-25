<?php

use BradieTilley\Builder\Exceptions\InvalidPhpDefinitionException;
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpPropertyGetHook;
use BradieTilley\Builder\PhpPropertySetHook;

test('abstract property with stub hooks', function () {
    $property = new PhpProperty(
        abstract: true,
        type: 'string',
        name: 'title',
        get: new PhpPropertyGetHook(stub: true),
    );

    expect($property->toPhp())->toBe(<<<'PHP'
abstract public string $title {
    get;
}
PHP);
});

test('final property', function () {
    $property = new PhpProperty(
        final: true,
        type: 'string',
        name: 'title',
        defaultValue: "'x'",
    );

    expect($property->toPhp())->toBe("final public string \$title = 'x';");
});

test('short expression hooks and by-ref get', function () {
    $property = new PhpProperty(
        type: 'string',
        name: 'name',
        get: new PhpPropertyGetHook(
            byRef: true,
            expression: '$this->name',
        ),
        set: new PhpPropertySetHook(
            type: 'string',
            expression: '$this->name = strtolower($value)',
        ),
    );

    expect($property->toPhp())->toBe(<<<'PHP'
public string $name {
    &get => $this->name;
    set(string $value) => $this->name = strtolower($value);
}
PHP);
});

test('interface-style get and set stubs', function () {
    $property = new PhpProperty(
        type: 'string',
        name: 'slug',
        get: new PhpPropertyGetHook(stub: true),
        set: new PhpPropertySetHook(stub: true),
    );

    expect($property->toPhp())->toBe(<<<'PHP'
public string $slug {
    get;
    set;
}
PHP);
});

test('abstract property without hooks is rejected', function () {
    (new PhpProperty(abstract: true, type: 'string', name: 'x'))->toPhp();
})->throws(InvalidPhpDefinitionException::class);
