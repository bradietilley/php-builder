# Classes

`PhpClass` is the primary file builder. Call `toPhp()` with no indent to emit a
complete PHP file (opening tag, strict types, namespace, imports, and the class
body). Nested/indented output is also supported when you pass `$indent > 0`.

```php
use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\PhpMethod;

$class = new PhpClass(
    namespace: 'App\\Models',
    name: 'Post',
    extends: 'App\\Models\\Model',
    implements: [
        'App\\Contracts\\Sluggable',
    ],
    traits: [
        'App\\Models\\Concerns\\HasSlug',
    ],
    description: 'A blog post',
    abstract: false,
    final: false,
    readonly: false,
    strictTypes: true,
);

echo $class->toPhp();
```

## Constructor options

| Argument | Type | Default | Notes |
| --- | --- | --- | --- |
| `name` | `string` | — | Short class name |
| `namespace` | `string` | `''` | Empty omits the `namespace` declaration |
| `extends` | `?string` | `null` | FQCN or short name |
| `implements` | `list<string>\|string` | `[]` | One interface or a list |
| `traits` | `list<PhpUseTrait\|string>` | `[]` | Strings become simple `use Trait;` |
| `constants` | `list<PhpClassConstant>` | `[]` | See [Attributes & Constants](../attributes/README.md) |
| `properties` | `list<PhpProperty>` | `[]` | See [Properties & Hooks](../properties/README.md) |
| `methods` | `list<PhpMethod>` | `[]` | See [Methods & Arguments](../methods/README.md) |
| `attributes` | `list<PhpAttribute>` | `[]` | Class-level attributes |
| `description` | `?string` | `null` | Class docblock summary |
| `templates` | `list<PhpTemplate\|string>` | `[]` | `@template` tags on the class docblock |
| `abstract` | `bool` | `false` | Mutually exclusive with `final` in practice (`abstract` wins in the signature) |
| `final` | `bool` | `false` | Emitted when not abstract |
| `readonly` | `bool` | `false` | `readonly class` |
| `strictTypes` | `bool` | `true` | Controls `declare(strict_types=1);` |

All of these are public properties, so you can append members after construction:

```php
$class->methods[] = new PhpMethod(name: 'boot', visibility: PhpMethod::protected());
$class->properties[] = new PhpProperty(type: 'string', name: 'title');
```

## Class modifiers

```php
new PhpClass(name: 'Base', abstract: true);
new PhpClass(name: 'Sealed', final: true);
new PhpClass(name: 'Config', readonly: true);
```

## Inheritance & traits

Pass FQCNs for `extends`, `implements`, and `traits`. They are reserved early so
later type imports alias correctly when basenames collide (e.g. your
`App\Models\Model` parent vs `Illuminate\Database\Eloquent\Model` in a return
type).

For trait adaptations (`as` / `insteadof`), pass a `PhpUseTrait` instead of a
string — see [Traits](../traits/README.md#using-traits-with-adaptations).

## Generics templates

```php
use BradieTilley\Builder\PhpTemplate;

new PhpClass(
    name: 'Collection',
    templates: [
        new PhpTemplate(name: 'TKey', of: 'array-key', covariant: true),
        new PhpTemplate(name: 'TValue'),
        'TExtra', // string shorthand → @template TExtra
    ],
);
```

## Mutating after construction

Builders are ordinary objects. A common pattern is to create the class, call
[`import()`](../imports/README.md) to reserve an alias for use inside method
bodies, then push methods that reference that alias:

```php
$class = new PhpClass(namespace: 'App\\Models', name: 'Post');

$tagQuery = $class->import('App\\Support\\TagQuery'); // "TagQuery" or an alias

$class->methods[] = new PhpMethod(
    name: 'syncTags',
    return: 'static',
    lines: [
        "\$query = {$tagQuery}::make(\$tags);",
        'return $this;',
    ],
);
```

## File output

`toPhp()` (with default indent `0`) produces:

1. `<?php`
2. Optional `declare(strict_types=1);`
3. Optional `namespace …;`
4. Sorted `use` lines from the import bag
5. Class attributes, signature, and body sections (traits → constants →
   properties → methods), separated by blank lines

Optionally pipe the result through [`PhpFormatter`](../formatting/README.md).

Continue to [Interfaces](../interfaces/README.md), or jump to
[Methods & Arguments](../methods/README.md).
