<?php

use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpAttribute;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\Types\PhpArrayType;

test('method exports visibility final and body lines', function () {
    $method = new PhpMethod(
        visibility: PhpMethod::VISIBILITY_PUBLIC,
        final: true,
        name: 'setTags',
        args: [
            new PhpArgument(
                type: new PhpArrayType(value: 'string'),
                name: 'tags',
                defaultValue: '[]',
            ),
        ],
        return: 'self',
        lines: [
            '$this->tags = $tags;',
            'return $this;',
        ],
        description: 'Set the tags',
    );

    expect($method->toPhp())->toBe(<<<'PHP'
/**
 * Set the tags
 *
 * @param array<string> $tags
 */
final public function setTags(array $tags = []): self
{
    $this->tags = $tags;
    return $this;
}
PHP);
});

test('abstract and signature-only methods end with semicolon', function () {
    $abstract = new PhpMethod(
        name: 'handle',
        abstract: true,
        return: 'void',
    );

    $signature = new PhpMethod(
        name: 'handle',
        signatureOnly: true,
        return: 'void',
    );

    expect($abstract->toPhp())->toBe('abstract public function handle(): void;')
        ->and($signature->toPhp())->toBe('public function handle(): void;');
});

test('method exports attributes', function () {
    $method = new PhpMethod(
        name: 'run',
        attributes: [new PhpAttribute('Override')],
        lines: ['//'],
    );

    expect($method->toPhp())->toContain('#[Override]')
        ->and($method->toPhp())->toContain('public function run()');
});
