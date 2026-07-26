# Types

Anywhere a type is accepted (`PhpProperty::$type`, `PhpArgument::$type`,
`PhpMethod::$return`, enum `backedType`, etc.) you may pass:

- a **string** — becomes a `PhpNamedType` (`'string'`, `'App\\Models\\Post'`, `'static'`)
- a **`PhpType` instance** — for generics, unions, intersections, callables
- `null` — omit the type

Strings that look like FQCNs (`Contains\\Backslash`) are imported automatically
when the containing member is resolved.

## Named types

```php
use BradieTilley\Builder\Types\PhpNamedType;

new PhpNamedType('string');
new PhpNamedType('Closure', nullable: true); // ?Closure

// Synonyms normalised: integer→int, boolean→bool, double→float
new PhpNamedType('integer'); // int
```

Nullable named types render as `?Name` in PHP and `Name|null` in PHPDoc.

## Generic types

Use `PhpGeneric` anywhere a type is accepted — properties, parameters, return
types, and so on. Native PHP gets the base type; key/value type parameters live
in PHPDoc (`@var` / `@param` / `@return`).

```php
use BradieTilley\Builder\Types\PhpGeneric;

PhpGeneric::array(value: 'string');
// PHP: array   PHPDoc: array<string>

PhpGeneric::array(key: 'array-key', value: 'string');
// PHP: array   PHPDoc: array<array-key, string>

PhpGeneric::list(value: 'string', nullable: true);
// PHP: ?array  PHPDoc: list<string>|null

PhpGeneric::iterable(value: 'int');
// PHP: iterable   PHPDoc: iterable<int>

PhpGeneric::for('Illuminate\\Support\\Collection', key: 'string', value: 'int');
// PHP: Collection   PHPDoc: Collection<string, int>
```

| Factory | Native PHP | Notes |
| --- | --- | --- |
| `::array(...)` | `array` | Optional `key`; value-only → `array<V>` |
| `::list(...)` | `array` | `list` is PHPDoc-only |
| `::iterable(...)` | `iterable` | Optional `key` |
| `::for($name, ...)` | `$name` (imported if FQCN) | Arbitrary class / built-in generic |

All factories accept `nullable: true` (`?Type` natively, `…\|null` in PHPDoc).
`needsPhpDoc()` is always true, so doc tags are emitted on members.

This is separate from declaring type parameters with [`PhpTemplate`](../attributes/README.md)
(`@template` on classes/methods). `PhpGeneric` is for *using* generics in member
types; `PhpTemplate` is for *declaring* them.

## Union types

```php
use BradieTilley\Builder\Types\PhpUnionType;

new PhpUnionType(['string', 'int']);
// string|int

new PhpUnionType(['string'], nullable: true);
// string|null

new PhpUnionType([
    'App\\Contracts\\Identifiable',
    'null',
]);
```

## Intersection types

```php
use BradieTilley\Builder\Types\PhpIntersectionType;

new PhpIntersectionType([
    'App\\Contracts\\Identifiable',
    'App\\Contracts\\Sluggable',
]);
// Identifiable&Sluggable
```

## Callable types

Native PHP emits `callable` or `Closure`; the parameter/return shape goes to
PHPDoc.

```php
use BradieTilley\Builder\Types\PhpCallableType;

new PhpCallableType(
    parameters: ['mixed'],
    return: 'bool',
);
// PHP: callable
// PHPDoc: callable(mixed): bool

new PhpCallableType(
    parameters: ['string'],
    return: 'bool',
    useClosure: true,
    nullable: true,
);
// PHP: ?Closure
// PHPDoc: callable(string): bool|null
```

| Argument | Notes |
| --- | --- |
| `parameters` | List of types for the PHPDoc callable signature |
| `return` | PHPDoc return type |
| `useClosure` | Use `Closure` instead of `callable` in native PHP |
| `nullable` | Prefix `?` / append `\|null` |

## Choosing string vs object

Prefer a plain string for scalars and simple class names. Reach for type objects
when you need shapes that PHP cannot express natively, or when you need
nullability / `Closure` vs `callable` control.

```php
// Fine
return: 'string';
type: 'App\\Models\\Post';

// Prefer objects
type: PhpGeneric::array(value: 'string');
type: PhpGeneric::for('Illuminate\\Support\\Collection', value: 'Post');
type: new PhpUnionType(['string', 'int']);
type: new PhpCallableType(parameters: ['self'], return: 'void');
```

Continue to [Imports & Aliasing](../imports/README.md).
