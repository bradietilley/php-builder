# Types

Anywhere a type is accepted (`PhpProperty::$type`, `PhpArgument::$type`,
`PhpMethod::$return`, enum `backedType`, etc.) you may pass:

- a **string** — becomes a `PhpNamedType` (`'string'`, `'App\\Models\\Post'`, `'static'`)
- a **`PhpType` instance** — for arrays, unions, intersections, callables
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

## Array types

Native PHP only has `array`. Element (and optional key) types live in PHPDoc.

```php
use BradieTilley\Builder\Types\PhpArrayType;

new PhpArrayType(value: 'string');
// PHP: array   PHPDoc: array<string>

new PhpArrayType(value: 'Illuminate\\Support\\Collection', key: 'string');
// PHP: array   PHPDoc: array<string, Collection>

new PhpArrayType(value: 'string', nullable: true);
// PHP: ?array  PHPDoc: array<string>|null
```

`needsPhpDoc()` is always true for array types, so `@param` / `@var` / `@return`
tags are emitted when the type is used on members.

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
type: new PhpArrayType(value: 'string');
type: new PhpUnionType(['string', 'int']);
type: new PhpCallableType(parameters: ['self'], return: 'void');
```

Continue to [Imports & Aliasing](../imports/README.md).
