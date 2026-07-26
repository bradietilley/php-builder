# PHP Target

Some syntax is only valid from a given PHP version. `PhpTarget` lets generators
declare the language level they are emitting for, so version-gated features are
included or omitted automatically.

The package default target is **8.3.0** (the minimum supported runtime),
independent of the PHP version currently executing the builder.

```php
use BradieTilley\Builder\Support\PhpFeature;
use BradieTilley\Builder\Support\PhpTarget;

PhpTarget::version(); // '8.3.0' by default

PhpTarget::using('8.5');
PhpTarget::supports(PhpFeature::FinalPromotedProperties); // true

PhpTarget::using(null); // back to default
// or
PhpTarget::clear();
```

## API

| Method | Purpose |
| --- | --- |
| `PhpTarget::using(?string $version)` | Set target (`'8.5'`, `'8.5.0'`, `'v8.4'`…); `null` clears |
| `PhpTarget::clear()` | Reset to default |
| `PhpTarget::version()` | Effective target (`DEFAULT` when unset) |
| `PhpTarget::current()` | Normalised host `PHP_VERSION` |
| `PhpTarget::supports(PhpFeature $feature)` | Whether the target is ≥ the feature’s `since()` |

Short versions like `8.5` are normalised to `8.5.0`.

## Features

`PhpFeature` enumerates gated syntax. Today:

| Case | Available since | Effect when unsupported |
| --- | --- | --- |
| `FinalPromotedProperties` | 8.5.0 | `final` on promoted constructor properties is omitted |

```php
use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\Support\PhpTarget;

PhpTarget::using('8.4');

$arg = new PhpArgument(
    visibility: PhpArgument::public(),
    final: true,
    type: 'string',
    name: 'name',
);

$arg->toPhp(); // "public string $name" — final stripped

PhpTarget::using('8.5');
$arg->toPhp(); // "final public string $name"
```

As more language features are added to the builder, new `PhpFeature` cases will
gate them the same way.

## Recommendations

- Set the target once at the start of a generation run to match the lowest PHP
  version your emitted code must run on.
- Clear it in test `afterEach` hooks so cases do not interfere with each other.
- Do not confuse `PhpTarget` with the runtime requirement of *this* package
  (still PHP 8.3+ to *run* the builder).

Continue to [Compatibility](../compatibility/README.md).
