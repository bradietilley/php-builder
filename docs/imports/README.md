# Imports & Aliasing

Every file-level builder (`PhpClass`, `PhpInterface`, `PhpTrait`, `PhpEnum`) owns
an `ImportBag`. When you call `toPhp()`, FQCNs discovered on the structure are
turned into sorted `use` statements, and type references are rewritten to short
or aliased names.

## What gets imported

Automatically reserved / imported from:

- `extends`, `implements`, and trait uses (including adaptation trait names)
- Attribute class names
- Property / argument / return / constant / backed-enum types that contain `\`
- `@throws` exception FQCNs

Same-namespace siblings (e.g. `App\Models\Post` while generating under
`App\Models`) are **not** added as `use` lines — the short name is used directly
unless it already clashes.

## Manual imports for method bodies

Method `lines` are opaque strings. If a body needs a class that is not otherwise
referenced, import it explicitly and interpolate the returned name:

```php
$class = new PhpClass(namespace: 'App\\Models', name: 'Post', extends: 'App\\Models\\Model');

// "Model" is already taken by extends → automatic clash alias ("ModelEloquent")
// Or pass a second argument to choose the alias yourself:
$name = $class->import('Illuminate\\Database\\Eloquent\\Model', 'EloquentModel');

$class->methods[] = new PhpMethod(
    name: 'asEloquent',
    return: 'Illuminate\\Database\\Eloquent\\Model', // also imports / reuses alias
    lines: [
        "return {$name};", // use the same alias in the body
    ],
);
```

`import()` returns the usable short name (or alias) and registers the `use` line.
The optional second argument forces that alias instead of the automatic naming
convention.

## Clash aliasing

When two FQCNs share a basename, the bag builds an alias by appending preceding
namespace segments, then a numeric suffix if needed:

| Competing FQCNs | Typical aliases |
| --- | --- |
| `App\Models\Model` (extends) + `Illuminate\Database\Eloquent\Model` | `Model` + `ModelEloquent` |
| Multiple remaining clashes | `ModelEloquent2`, … |

Structural names (`extends` / `implements` / traits) are reserved **first**, so
user imports lose the basename when they collide.

## Import bag API

You rarely need this directly, but it is available via `$class->imports()`:

```php
$bag = $class->imports();

$bag->import('App\\Support\\TagQuery');              // "TagQuery"
$bag->import('Other\\TagQuery', 'OtherTagQuery');    // forced alias
$bag->reserve('App\\Models\\Model');                 // same as import; used internally
$bag->toUseLines();                                  // ["use App\\Support\\TagQuery;", …]
$bag->all();                                         // fqcn => alias|null
```

## Practical tips

1. Prefer FQCNs in builder type fields — let the bag shorten them.
2. Call `import()` before composing body lines that mention a class.
3. Pass a second argument to `import($fqcn, $alias)` when you want a specific
   name instead of the automatic clash alias.
4. Reuse the string returned by `import()` (or by a type field that already
   imported the same FQCN) so bodies stay consistent with `use` aliases.
5. Attribute argument expressions are **not** scanned for FQCNs — only the
   attribute class name is. Put fully qualified names or already-imported short
   names inside argument expression strings yourself.

Continue to [Attributes & Constants](../attributes/README.md).
