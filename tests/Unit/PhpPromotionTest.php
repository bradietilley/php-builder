<?php

use BradieTilley\Builder\Exceptions\InvalidPhpDefinitionException;
use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\PhpPropertyGetHook;
use BradieTilley\Builder\PhpPropertySetHook;

test('visibility on argument implies constructor promotion', function () {
    $arg = new PhpArgument(
        visibility: PhpArgument::VISIBILITY_PUBLIC,
        readonly: true,
        type: 'string',
        name: 'title',
    );

    expect($arg->isPromoted())->toBeTrue()
        ->and($arg->toPhp())->toBe('public readonly string $title');
});

test('promoted argument supports asymmetric set visibility and final', function () {
    $arg = new PhpArgument(
        visibility: PhpArgument::VISIBILITY_PUBLIC,
        setVisibility: PhpArgument::VISIBILITY_PRIVATE,
        final: true,
        type: 'string',
        name: 'name',
        defaultValue: "'x'",
    );

    expect($arg->toPhp())->toBe("final public private(set) string \$name = 'x'");
});

test('promoted argument supports property hooks', function () {
    $arg = new PhpArgument(
        visibility: PhpArgument::VISIBILITY_PUBLIC,
        type: 'string',
        name: 'name',
        get: new PhpPropertyGetHook(lines: ['return $this->name;']),
        set: new PhpPropertySetHook(
            type: 'string',
            name: 'value',
            lines: ['$this->name = $value;'],
        ),
    );

    expect($arg->toPhp())->toBe(<<<'PHP'
public string $name {
    get {
        return $this->name;
    }
    set(string $value) {
        $this->name = $value;
    }
}
PHP);
});

test('constructor method exports promoted parameters', function () {
    $method = new PhpMethod(
        name: '__construct',
        args: [
            new PhpArgument(
                visibility: PhpArgument::VISIBILITY_PUBLIC,
                readonly: true,
                type: 'string',
                name: 'title',
            ),
            new PhpArgument(
                visibility: PhpArgument::VISIBILITY_PUBLIC,
                setVisibility: PhpArgument::VISIBILITY_PRIVATE,
                type: 'string',
                name: 'slug',
            ),
        ],
    );

    expect($method->toPhp())->toBe(<<<'PHP'
public function __construct(
    public readonly string $title,
    public private(set) string $slug,
) {
}
PHP);
});

test('hooks on non-promoted arguments are rejected', function () {
    $arg = new PhpArgument(
        type: 'string',
        name: 'name',
        get: new PhpPropertyGetHook(lines: ['return $name;']),
    );

    $arg->toPhp();
})->throws(InvalidPhpDefinitionException::class);
