# Traits

`PhpTrait` emits a trait file. Traits can compose other traits (including
`as` / `insteadof` adaptations), declare constants, properties, and methods —
the same member builders used by classes.

```php
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpTrait;

$trait = new PhpTrait(
    namespace: 'App\\Concerns',
    name: 'HasSlug',
    properties: [
        new PhpProperty(type: 'string', name: 'slug'),
    ],
    methods: [
        new PhpMethod(
            name: 'bootHasSlug',
            visibility: PhpMethod::protected(),
            lines: ['// …'],
        ),
    ],
    description: 'Slug helpers',
);

echo $trait->toPhp();
```

## Constructor options

| Argument | Type | Default | Notes |
| --- | --- | --- | --- |
| `name` | `string` | — | |
| `namespace` | `string` | `''` | |
| `traits` | `list<PhpUseTrait\|string>` | `[]` | Nested trait uses |
| `constants` | `list<PhpClassConstant>` | `[]` | |
| `properties` | `list<PhpProperty>` | `[]` | May include abstract hook properties |
| `methods` | `list<PhpMethod>` | `[]` | |
| `attributes` | `list<PhpAttribute>` | `[]` | |
| `description` | `?string` | `null` | |
| `templates` | `list<PhpTemplate\|string>` | `[]` | |
| `strictTypes` | `bool` | `true` | |

## Using traits with adaptations

Both `PhpClass` and `PhpTrait` accept trait uses as plain FQCN strings or as
`PhpUseTrait` objects when you need aliases / conflict resolution.

### Simple use

```php
traits: [
    'App\\Concerns\\HasTimestamps',
]
```

### Shared use block with `as` and `insteadof`

```php
use BradieTilley\Builder\PhpTraitAlias;
use BradieTilley\Builder\PhpTraitInsteadof;
use BradieTilley\Builder\PhpUseTrait;

new PhpUseTrait(
    name: [
        'App\\Concerns\\HasSlug',
        'App\\Concerns\\HasUuid',
    ],
    aliases: [
        new PhpTraitAlias(
            method: 'bootHasSlug',
            alias: 'bootSlug',
            visibility: PhpTraitAlias::protected(),
            trait: 'App\\Concerns\\HasSlug',
        ),
        // Shorthand: method => alias
        // 'log' => 'writeLog',
    ],
    insteadof: [
        new PhpTraitInsteadof(
            method: 'boot',
            from: 'App\\Concerns\\HasSlug',
            insteadOf: 'App\\Concerns\\HasUuid',
        ),
        // Shorthand (from defaults to the first trait in `name`):
        // 'track' => 'App\\Concerns\\TracksChanges',
    ],
);
```

Emits something like:

```php
use HasSlug, HasUuid {
    HasSlug::boot insteadof HasUuid;
    HasSlug::bootHasSlug as protected bootSlug;
}
```

### `PhpTraitAlias`

| Argument | Notes |
| --- | --- |
| `method` | Method being aliased |
| `alias` | New name (optional if only changing visibility) |
| `visibility` | Optional `public` / `protected` / `private` |
| `trait` | Optional qualifying trait name (`Trait::method as …`) |

### `PhpTraitInsteadof`

| Argument | Notes |
| --- | --- |
| `method` | Conflicting method |
| `from` | Winning trait |
| `insteadOf` | Trait to exclude for that method |

Trait FQCNs in adaptations are resolved through the same import bag as the
containing type, so short names appear in the adaptation block after import.

## Abstract properties

Traits may declare abstract properties that require hooks:

```php
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpPropertyGetHook;

new PhpProperty(
    abstract: true,
    type: 'string',
    name: 'key',
    get: new PhpPropertyGetHook(stub: true),
);
```

Continue to [Enums](../enums/README.md).
