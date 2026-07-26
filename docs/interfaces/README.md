# Interfaces

`PhpInterface` emits a full interface file. Methods are forced to
**signature-only** form (no bodies, no `abstract` keyword) regardless of how you
configure the `PhpMethod` instances.

```php
use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpInterface;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpPropertyGetHook;
use BradieTilley\Builder\Types\PhpUnionType;

$interface = new PhpInterface(
    namespace: 'App\\Contracts',
    name: 'Identifiable',
    extends: [
        'Stringable',
    ],
    properties: [
        new PhpProperty(
            type: 'string',
            name: 'id',
            get: new PhpPropertyGetHook(stub: true),
        ),
    ],
    methods: [
        new PhpMethod(
            name: 'resolve',
            args: [
                new PhpArgument(
                    type: new PhpUnionType(['string', 'int']),
                    name: 'key',
                ),
            ],
            return: 'static',
            description: 'Resolve by key',
            // lines are ignored for interfaces — signature only
            lines: ['return $this;'],
        ),
    ],
    description: 'Something with an identity',
);

echo $interface->toPhp();
```

## Constructor options

| Argument | Type | Default | Notes |
| --- | --- | --- | --- |
| `name` | `string` | — | Short interface name |
| `namespace` | `string` | `''` | |
| `extends` | `list<string>\|string` | `[]` | Parent interfaces |
| `constants` | `list<PhpClassConstant>` | `[]` | |
| `properties` | `list<PhpProperty>` | `[]` | Typically hook stubs for interface properties |
| `methods` | `list<PhpMethod>` | `[]` | Bodies stripped; rendered as signatures |
| `attributes` | `list<PhpAttribute>` | `[]` | |
| `description` | `?string` | `null` | |
| `templates` | `list<PhpTemplate\|string>` | `[]` | |
| `strictTypes` | `bool` | `true` | |

## Interface properties

PHP interfaces may declare properties with hook stubs. Use
`PhpPropertyGetHook(stub: true)` / `PhpPropertySetHook(stub: true)`:

```php
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpPropertyGetHook;
use BradieTilley\Builder\PhpPropertySetHook;

new PhpProperty(
    type: 'string',
    name: 'name',
    get: new PhpPropertyGetHook(stub: true),
    set: new PhpPropertySetHook(stub: true),
);
```

Emits:

```php
public string $name {
    get;
    set;
}
```

See [Properties & Hooks](../properties/README.md) for full hook options.

## Method signatures

Any `lines` you set on interface methods are ignored in the emitted PHP. Return
types, arguments, templates, `@throws`, attributes, and descriptions still
render on the signature / docblock.

Continue to [Traits](../traits/README.md).
