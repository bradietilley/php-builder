# Enums

`PhpEnum` emits pure or backed enums, with optional interfaces, cases,
constants, and methods.

## Pure enum

Omit `backedType` (or pass `null`):

```php
use BradieTilley\Builder\PhpEnum;
use BradieTilley\Builder\PhpEnumCase;
use BradieTilley\Builder\PhpMethod;

$enum = new PhpEnum(
    namespace: 'App\\Enums',
    name: 'Role',
    cases: [
        new PhpEnumCase(name: 'Admin'),
        new PhpEnumCase(name: 'Editor'),
        new PhpEnumCase(name: 'Viewer'),
    ],
    methods: [
        new PhpMethod(
            name: 'isStaff',
            return: 'bool',
            lines: [
                'return $this === self::Admin || $this === self::Editor;',
            ],
        ),
    ],
    description: 'Application roles',
);

echo $enum->toPhp();
```

## Backed enum

Pass `backedType` as a string or `PhpType` (`'string'`, `'int'`, etc.):

```php
use BradieTilley\Builder\PhpAttribute;
use BradieTilley\Builder\PhpEnum;
use BradieTilley\Builder\PhpEnumCase;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\Types\PhpNamedType;

$enum = new PhpEnum(
    namespace: 'App\\Enums',
    name: 'Status',
    backedType: new PhpNamedType('string'),
    implements: [
        'App\\Contracts\\Labelled',
        'JsonSerializable',
    ],
    cases: [
        new PhpEnumCase(
            name: 'Draft',
            value: "'draft'",
            attributes: [
                new PhpAttribute('App\\Attributes\\Colour', ["value: 'grey'"]),
            ],
        ),
        new PhpEnumCase(name: 'Published', value: "'published'"),
    ],
    methods: [
        new PhpMethod(
            name: 'label',
            return: 'string',
            lines: [
                'return match ($this) {',
                "    self::Draft => 'Draft',",
                "    self::Published => 'Published',",
                '};',
            ],
        ),
    ],
);
```

> **Case values** are raw PHP expressions as strings (or bare ints). Quote string
> literals yourself: `value: "'draft'"` — not `value: 'draft'`.

## Constructor options

| Argument | Type | Default | Notes |
| --- | --- | --- | --- |
| `name` | `string` | — | |
| `namespace` | `string` | `''` | |
| `backedType` | `PhpType\|string\|null` | `null` | Presence makes it a backed enum |
| `implements` | `list<string>\|string` | `[]` | |
| `cases` | `list<PhpEnumCase>` | `[]` | |
| `constants` | `list<PhpClassConstant>` | `[]` | |
| `methods` | `list<PhpMethod>` | `[]` | Instance or `static` methods |
| `attributes` | `list<PhpAttribute>` | `[]` | |
| `description` | `?string` | `null` | |
| `templates` | `list<PhpTemplate\|string>` | `[]` | Docblock generics |
| `strictTypes` | `bool` | `true` | Set `false` to omit the declare |

## Enum cases

```php
new PhpEnumCase(
    name: 'Draft',
    value: "'draft'", // null for pure cases
    attributes: [],
);
```

## Disabling strict types

```php
new PhpEnum(
    name: 'Role',
    cases: [new PhpEnumCase(name: 'Admin')],
    strictTypes: false,
);
```

Continue to [Methods & Arguments](../methods/README.md).
