<?php

use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpAttribute;
use BradieTilley\Builder\Types\PhpGeneric;

test('attribute exports with and without arguments', function () {
    expect((new PhpAttribute('Deprecated'))->toPhp())->toBe('#[Deprecated]')
        ->and((new PhpAttribute('Route', ["'/posts'", "methods: ['GET']"]))->toPhp())
        ->toBe("#[Route('/posts', methods: ['GET'])]");
});

test('argument exports type name and default', function () {
    $arg = new PhpArgument(
        type: PhpGeneric::array(value: 'string'),
        name: 'tags',
        defaultValue: '[]',
    );

    expect($arg->toPhp())->toBe('array $tags = []')
        ->and($arg->phpDocParamLine())->toBe('@param array<string> $tags');
});

test('argument iterable generic exports param phpdoc', function () {
    $arg = new PhpArgument(
        type: PhpGeneric::iterable(value: 'int'),
        name: 'ids',
    );

    expect($arg->toPhp())->toBe('iterable $ids')
        ->and($arg->phpDocParamLine())->toBe('@param iterable<int> $ids');
});

test('argument supports attributes and indent', function () {
    $arg = new PhpArgument(
        type: 'string',
        name: 'name',
        attributes: [new PhpAttribute('SensitiveParameter')],
    );

    expect($arg->toPhp(1))->toBe("    #[SensitiveParameter]\n    string \$name");
});
