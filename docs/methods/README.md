# Methods & Arguments

Methods are `PhpMethod` objects. Arguments are `PhpArgument` objects. Both accept
native types as strings or richer [`PhpType`](../types/README.md) instances.

```php
use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpAttribute;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\Types\PhpGeneric;

new PhpMethod(
    name: 'setTags',
    visibility: PhpMethod::public(),
    final: true,
    args: [
        new PhpArgument(
            type: PhpGeneric::array(value: 'string'),
            name: 'tags',
            defaultValue: '[]',
            description: 'Tag names',
        ),
    ],
    return: 'static',
    lines: [
        '$this->tags()->sync($tags);',
        'return $this;',
    ],
    description: 'Sync tags on the model',
    throws: ['RuntimeException'],
    attributes: [
        new PhpAttribute('App\\Attributes\\Internal'),
    ],
);
```

## Method options

| Argument | Type | Default | Notes |
| --- | --- | --- | --- |
| `name` | `string` | — | |
| `visibility` | `PhpVisibility` | `Public` | Use `PhpMethod::public()` / `protected()` / `private()` |
| `static` | `bool` | `false` | |
| `final` | `bool` | `false` | Ignored when `abstract` is true |
| `abstract` | `bool` | `false` | Signature ends with `;`, no body |
| `returnsReference` | `bool` | `false` | `function &name()` |
| `args` | `list<PhpArgument>` | `[]` | Multi-arg methods render multiline |
| `return` | `PhpType\|string\|null` | `null` | |
| `lines` | `list<string>` | `[]` | Raw body lines (no leading indent) |
| `description` | `?string` | `null` | Docblock summary |
| `throws` | `list<string>` | `[]` | FQCNs become `@throws` (and are imported) |
| `templates` | `list<PhpTemplate\|string>` | `[]` | Method-level `@template` tags |
| `attributes` | `list<PhpAttribute>` | `[]` | |
| `signatureOnly` | `bool` | `false` | Force `;` with no body (interfaces set this for you) |

Empty `lines` entries become blank lines in the method body.

### Abstract methods

```php
new PhpMethod(
    name: 'boot',
    visibility: PhpMethod::protected(),
    abstract: true,
    return: 'void',
);
```

### Returning by reference

```php
new PhpMethod(name: 'resolve', returnsReference: true, return: 'mixed', lines: ['return $x;']);
```

### Generics on methods

```php
use BradieTilley\Builder\PhpTemplate;
use BradieTilley\Builder\Types\PhpCallableType;
use BradieTilley\Builder\Types\PhpGeneric;

new PhpMethod(
    name: 'map',
    static: true,
    templates: [
        new PhpTemplate(name: 'TReturn', of: 'mixed'),
    ],
    args: [
        new PhpArgument(
            type: new PhpCallableType(parameters: ['mixed'], return: 'TReturn'),
            name: 'callback',
        ),
    ],
    return: PhpGeneric::array(value: 'TReturn'),
    lines: ['return [];'],
);
```

## Arguments

```php
new PhpArgument(
    name: 'tags',
    type: 'array',
    defaultValue: '[]',
    variadic: false,
    byRef: false,
    visibility: null,       // set → constructor promotion
    setVisibility: null,    // asymmetric set on promoted props
    readonly: false,
    final: false,           // PHP 8.5+ when PhpTarget allows
    description: null,
    get: null,              // promoted property hooks
    set: null,
    attributes: [],
);
```

| Argument | Notes |
| --- | --- |
| `defaultValue` | Raw PHP expression string (`'[]'`, `'null'`, `"'untitled'"`) |
| `variadic` | Emits `...$name`; default values are omitted |
| `byRef` | Emits `&$name` |
| `visibility` | Any visibility implies **constructor property promotion** |
| `promoted` | Also available; setting visibility sets this automatically |
| `setVisibility` | Asymmetric visibility: `public private(set)` |
| `final` | Only on promoted params; gated by [PHP Target](../php-target/README.md) 8.5+ |
| `get` / `set` | Property hooks on promoted parameters only |
| `attributes` | Parameter attributes (e.g. `#[SensitiveParameter]`) |

### Constructor promotion

```php
new PhpMethod(
    name: '__construct',
    args: [
        new PhpArgument(
            visibility: PhpArgument::public(),
            readonly: true,
            type: 'string',
            name: 'id',
        ),
        new PhpArgument(
            visibility: PhpArgument::public(),
            setVisibility: PhpArgument::private(),
            type: 'string',
            name: 'name',
            defaultValue: "'untitled'",
        ),
        new PhpArgument(
            type: 'int',
            name: 'count',
            byRef: true, // ordinary (non-promoted) by-ref param
        ),
        new PhpArgument(
            type: 'string',
            name: 'tags',
            variadic: true,
        ),
    ],
    lines: [
        '// …',
    ],
);
```

Hooks, asymmetric set visibility, and `final` are **only** valid on promoted
parameters. Using them on a normal argument throws
`InvalidPhpDefinitionException`.

### Promoted property hooks

```php
use BradieTilley\Builder\PhpPropertyGetHook;
use BradieTilley\Builder\PhpPropertySetHook;

new PhpArgument(
    visibility: PhpArgument::protected(),
    type: 'string',
    name: 'nickname',
    get: new PhpPropertyGetHook(lines: ['return $this->nickname;']),
    set: new PhpPropertySetHook(
        type: 'string',
        lines: ['$this->nickname = trim($value);'],
    ),
);
```

See [Properties & Hooks](../properties/README.md) for expression vs block vs stub
forms.

### Visibility helpers

`PhpMethod`, `PhpArgument`, `PhpProperty`, `PhpClassConstant`, and `PhpTraitAlias`
all expose:

```php
PhpMethod::public();
PhpMethod::protected();
PhpMethod::private();
```

These return `PhpVisibility` enum cases.

Continue to [Properties & Hooks](../properties/README.md).
