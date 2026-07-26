# Properties & Hooks

`PhpProperty` models class/trait/interface properties, including asymmetric
visibility and PHP property hooks.

```php
use BradieTilley\Builder\PhpAttribute;
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\Types\PhpUnionType;

new PhpProperty(
    type: 'string',
    name: 'title',
    description: 'Display title',
    defaultValue: "''",
    attributes: [
        new PhpAttribute('App\\Attributes\\Column', ['length: 255']),
    ],
);

new PhpProperty(
    type: new PhpUnionType(['string', 'int']),
    name: 'code',
    visibility: PhpProperty::protected(),
    setVisibility: PhpProperty::private(),
);
```

## Property options

| Argument | Type | Default | Notes |
| --- | --- | --- | --- |
| `name` | `string` | — | Without `$` |
| `type` | `PhpType\|string\|null` | `null` | |
| `visibility` | `PhpVisibility` | `Public` | Get visibility |
| `setVisibility` | `?PhpVisibility` | `null` | When different → `public private(set)` |
| `static` | `bool` | `false` | |
| `readonly` | `bool` | `false` | Incompatible with hooks |
| `abstract` | `bool` | `false` | Requires at least one hook; no default |
| `final` | `bool` | `false` | Incompatible with `abstract` |
| `defaultValue` | `?string` | `null` | Raw PHP; omitted when hooks are present |
| `description` | `?string` | `null` | Docblock summary |
| `get` | `?PhpPropertyGetHook` | `null` | |
| `set` | `?PhpPropertySetHook` | `null` | |
| `attributes` | `list<PhpAttribute>` | `[]` | |

Types that need PHPDoc (e.g. `array<string>`) automatically add a `@var` tag.

## Asymmetric visibility

```php
new PhpProperty(
    type: 'string',
    name: 'code',
    visibility: PhpProperty::protected(),
    setVisibility: PhpProperty::private(),
);
```

```php
protected private(set) string $code;
```

## Property hooks

Hooks support three shapes: **stub**, **expression**, and **block**.

### Get hooks (`PhpPropertyGetHook`)

| Argument | Notes |
| --- | --- |
| `stub` | Emits `get;` (interfaces / abstract) |
| `expression` | Emits `get => $expr;` |
| `lines` | Emits a `{ … }` body |
| `byRef` | Emits `&get` |

```php
use BradieTilley\Builder\PhpPropertyGetHook;

// Stub
new PhpPropertyGetHook(stub: true);

// Expression
new PhpPropertyGetHook(byRef: true, expression: '$this->slug');

// Block
new PhpPropertyGetHook(lines: ['return strtoupper($this->title);']);
```

### Set hooks (`PhpPropertySetHook`)

| Argument | Notes |
| --- | --- |
| `stub` | Emits `set;` — no type/expression/lines allowed |
| `type` | **Required** unless stub |
| `name` | Parameter name (default `value`) |
| `expression` | Emits `set(Type $name) => $expr;` |
| `lines` | Emits a block body |

```php
use BradieTilley\Builder\PhpPropertySetHook;

new PhpPropertySetHook(
    type: 'string',
    expression: '$this->slug = strtolower($value)',
);

new PhpPropertySetHook(
    type: 'string',
    name: 'incoming',
    lines: ['$this->title = $incoming;'],
);
```

### Full example

```php
new PhpProperty(
    type: 'string',
    name: 'label',
    final: true,
    get: new PhpPropertyGetHook(lines: ['return strtoupper($this->title);']),
    set: new PhpPropertySetHook(
        type: 'string',
        name: 'incoming',
        lines: ['$this->title = $incoming;'],
    ),
);
```

```php
final public string $label {
    get {
        return strtoupper($this->title);
    }
    set(string $incoming) {
        $this->title = $incoming;
    }
}
```

## Validation rules

The builder throws `InvalidPhpDefinitionException` when:

- Hooks are combined with `readonly`
- A property is both `abstract` and `final`
- An abstract property has no hooks, or has a default value
- A stub hook also has an expression or body lines
- An expression hook also has body lines
- A non-stub set hook omits its parameter type

Continue to [Types](../types/README.md).
