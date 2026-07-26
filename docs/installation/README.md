# Installation

## Requirements

- PHP **8.3+**
- [`bradietilley/php-data`](https://bradietilley.dev/php-data) (installed automatically as a dependency)

The package is framework-agnostic. Laravel is only used in the test suite.

## Install

```bash
composer require bradietilley/php-builder
```

There is **no service provider to register and no config to publish** — the
package works as soon as it is autoloaded.

## Verifying the install

```php
use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\PhpMethod;

$php = (new PhpClass(
    namespace: 'App\\Support',
    name: 'Hello',
    methods: [
        new PhpMethod(
            name: 'greet',
            return: 'string',
            lines: ["return 'hi';"],
        ),
    ],
))->toPhp();

// Starts with "<?php" and contains "class Hello"
```

## Namespace

All public types live under `BradieTilley\Builder` (and
`BradieTilley\Builder\Types` for the type system,
`BradieTilley\Builder\Support` for targeting/imports helpers).

## Next steps

- [Classes](../classes/README.md) — generate your first class file
- [Types](../types/README.md) — unions, arrays, callables, and more
- [Imports & Aliasing](../imports/README.md) — how FQCNs become `use` lines
