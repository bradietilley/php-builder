# Attributes & Constants

## Attributes

`PhpAttribute` renders `#[Name]` / `#[Name(…)]` on classes, interfaces, traits,
enums, enum cases, properties, methods, constants, and parameters.

```php
use BradieTilley\Builder\PhpAttribute;

new PhpAttribute('AllowDynamicProperties');

new PhpAttribute(
    'App\\Attributes\\OwnedBy',
    ["team: 'platform'"],
);

new PhpAttribute('Deprecated', ["message: 'use NAME'"]);
```

| Argument | Notes |
| --- | --- |
| `name` | Short name or FQCN (FQCNs are imported) |
| `arguments` | **List of raw PHP expression strings**, joined with `, ` |

Examples of argument strings:

```php
["length: 255"]
["value: 'grey'"]
["group: 'content'"]
["'positional'", 'named: true']
```

The builder does not parse attribute arguments — whatever you pass is emitted
verbatim inside the parentheses.

### Where attributes attach

```php
// Type-level
new PhpClass(attributes: [new PhpAttribute('…')]);

// Constant
new PhpClassConstant(name: 'TYPE', value: "'x'", attributes: […]);

// Property / method / parameter / enum case — same pattern
```

## Class constants

`PhpClassConstant` works on classes, interfaces, traits, and enums.

```php
use BradieTilley\Builder\PhpAttribute;
use BradieTilley\Builder\PhpClassConstant;

new PhpClassConstant(
    name: 'TYPE',
    value: "'kitchen'",
    type: 'string',
    attributes: [
        new PhpAttribute('Deprecated', ["message: 'use NAME'"]),
    ],
);

new PhpClassConstant(
    name: 'MAX',
    value: '100',
    visibility: PhpClassConstant::protected(),
    final: true,
    type: 'int',
);

new PhpClassConstant(
    name: 'DEFAULT',
    value: 'self::Draft',
    type: 'self',
    visibility: PhpClassConstant::private(),
);
```

| Argument | Notes |
| --- | --- |
| `name` | Constant name |
| `value` | Raw PHP expression (`'100'`, `"'kitchen'"`, `'self::Draft'`) |
| `visibility` | Default `public` |
| `final` | `final public const …` |
| `type` | Typed class constants |
| `attributes` | |

## Templates (generics)

`PhpTemplate` (or a plain string) adds `@template` tags to type or method
docblocks.

```php
use BradieTilley\Builder\PhpTemplate;

new PhpTemplate(name: 'TValue');
new PhpTemplate(name: 'TKey', of: 'array-key', covariant: true);
new PhpTemplate(name: 'TInput', contravariant: true);

// String shorthand on templates arrays:
templates: ['TExtra'] // → @template TExtra
```

| Argument | Notes |
| --- | --- |
| `name` | Template name (`TValue`) |
| `of` | Optional bound (`of array-key`) |
| `covariant` | `@template covariant T` |
| `contravariant` | `@template contravariant T` |

`covariant` wins over `contravariant` if both were somehow set.

Continue to [Formatting](../formatting/README.md).
