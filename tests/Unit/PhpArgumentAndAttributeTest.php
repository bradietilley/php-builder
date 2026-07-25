<?php

use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpAttribute;
use BradieTilley\Builder\Types\PhpArrayType;

test('attribute exports with and without arguments', function () {
    expect((new PhpAttribute('Deprecated'))->toPhp())->toBe('#[Deprecated]')
        ->and((new PhpAttribute('Route', ["'/posts'", "methods: ['GET']"]))->toPhp())
        ->toBe("#[Route('/posts', methods: ['GET'])]");
});

test('argument exports type name and default', function () {
    $arg = new PhpArgument(
        type: new PhpArrayType(value: 'string'),
        name: 'tags',
        defaultValue: '[]',
    );

    expect($arg->toPhp())->toBe('array $tags = []')
        ->and($arg->phpDocParamLine())->toBe('@param array<string> $tags');
});

test('argument supports attributes and indent', function () {
    $arg = new PhpArgument(
        type: 'string',
        name: 'name',
        attributes: [new PhpAttribute('SensitiveParameter')],
    );

    expect($arg->toPhp(1))->toBe("    #[SensitiveParameter]\n    string \$name");
});
