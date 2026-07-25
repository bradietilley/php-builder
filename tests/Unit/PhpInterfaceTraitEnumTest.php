<?php

use BradieTilley\Builder\PhpEnum;
use BradieTilley\Builder\PhpEnumCase;
use BradieTilley\Builder\PhpInterface;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpPropertyGetHook;
use BradieTilley\Builder\PhpPropertySetHook;
use BradieTilley\Builder\PhpTrait;
use BradieTilley\Builder\Types\PhpNamedType;

test('interface exports signature-only methods', function () {
    $interface = new PhpInterface(
        namespace: 'App\\Contracts',
        name: 'WithSlug',
        extends: 'App\\Contracts\\Identifiable',
        methods: [
            new PhpMethod(name: 'slug', return: 'string', lines: ['return "";']),
        ],
    );

    expect($interface->toPhp())->toBe(<<<'PHP'
<?php

declare(strict_types=1);

namespace App\Contracts;

interface WithSlug extends Identifiable
{
    public function slug(): string;
}

PHP);
});

test('interface exports hooked properties', function () {
    $interface = new PhpInterface(
        namespace: 'App\\Contracts',
        name: 'HasName',
        properties: [
            new PhpProperty(
                type: 'string',
                name: 'name',
                get: new PhpPropertyGetHook(stub: true),
                set: new PhpPropertySetHook(stub: true),
            ),
        ],
    );

    expect($interface->toPhp())->toContain("public string \$name {\n        get;\n        set;\n    }");
});

test('trait exports properties and methods', function () {
    $trait = new PhpTrait(
        namespace: 'App\\Concerns',
        name: 'HasSlug',
        properties: [
            new PhpProperty(type: 'string', name: 'slug'),
        ],
        methods: [
            new PhpMethod(
                name: 'bootHasSlug',
                visibility: PhpMethod::VISIBILITY_PROTECTED,
                lines: ['//'],
            ),
        ],
    );

    expect($trait->toPhp())->toContain('trait HasSlug')
        ->and($trait->toPhp())->toContain('public string $slug;')
        ->and($trait->toPhp())->toContain('protected function bootHasSlug()');
});

test('enum exports backed cases and methods', function () {
    $enum = new PhpEnum(
        namespace: 'App\\Enums',
        name: 'Status',
        backedType: 'string',
        cases: [
            new PhpEnumCase(name: 'Draft', value: "'draft'"),
            new PhpEnumCase(name: 'Published', value: "'published'"),
        ],
        methods: [
            new PhpMethod(
                name: 'label',
                return: 'string',
                lines: ['return $this->value;'],
            ),
        ],
    );

    expect($enum->toPhp())->toBe(<<<'PHP'
<?php

declare(strict_types=1);

namespace App\Enums;

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return $this->value;
    }
}

PHP);
});

test('pure enum without backing type', function () {
    $enum = new PhpEnum(
        namespace: 'App\\Enums',
        name: 'Role',
        cases: [
            new PhpEnumCase(name: 'Admin'),
            new PhpEnumCase(name: 'User'),
        ],
    );

    expect($enum->toPhp())->toContain('enum Role')
        ->and($enum->toPhp())->not->toContain('enum Role:')
        ->and($enum->toPhp())->toContain('case Admin;');
});

test('enum backed type accepts PhpType', function () {
    $enum = new PhpEnum(
        namespace: 'App\\Enums',
        name: 'Status',
        backedType: new PhpNamedType('int'),
        cases: [
            new PhpEnumCase(name: 'One', value: '1'),
        ],
    );

    expect($enum->toPhp())->toContain('enum Status: int');
});
