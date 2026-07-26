<?php

use BradieTilley\Builder\Exceptions\InvalidPhpDefinitionException;
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpPropertyGetHook;
use BradieTilley\Builder\PhpPropertySetHook;
use BradieTilley\Builder\Types\PhpArrayType;
use BradieTilley\Builder\Types\PhpUnionType;

test('property exports readonly and defaults', function () {
    $property = new PhpProperty(
        visibility: PhpProperty::protected(),
        readonly: true,
        type: 'string',
        name: 'title',
        defaultValue: "'untitled'",
    );

    expect($property->toPhp(1))->toBe("    protected readonly string \$title = 'untitled';");
});

test('property exports asymmetric visibility', function () {
    $property = new PhpProperty(
        visibility: PhpProperty::public(),
        setVisibility: PhpProperty::private(),
        type: 'string',
        name: 'name',
    );

    expect($property->toPhp())->toBe('public private(set) string $name;');
});

test('property exports get and set hooks with structured set signature', function () {
    $property = new PhpProperty(
        type: 'string',
        name: 'name',
        get: new PhpPropertyGetHook(lines: [
            'return strtoupper($this->name);',
        ]),
        set: new PhpPropertySetHook(
            type: new PhpUnionType(['string', 'Stringable']),
            name: 'value',
            lines: [
                '$this->name = (string) $value;',
            ],
        ),
    );

    expect($property->toPhp())->toBe(<<<'PHP'
public string $name {
    get {
        return strtoupper($this->name);
    }
    set(string|Stringable $value) {
        $this->name = (string) $value;
    }
}
PHP);
});

test('property phpdoc includes generics', function () {
    $property = new PhpProperty(
        type: new PhpArrayType(value: 'string'),
        name: 'tags',
        description: 'Tag list',
    );

    expect($property->toPhp())->toBe(<<<'PHP'
/**
 * Tag list
 *
 * @var array<string> $tags
 */
public array $tags;
PHP);
});

test('readonly properties cannot have hooks', function () {
    $property = new PhpProperty(
        readonly: true,
        type: 'string',
        name: 'name',
        get: new PhpPropertyGetHook(lines: ['return $this->name;']),
    );

    $property->toPhp();
})->throws(InvalidPhpDefinitionException::class);
