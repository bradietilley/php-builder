# Compatibility

## Supported versions

| PHP | Status |
| --- | --- |
| 8.3, 8.4, 8.5 | Supported |

The package itself requires PHP 8.3+. Generated syntax can be constrained further
with [`PhpTarget`](../php-target/README.md) so emitters stay compatible with a
chosen language level (for example, omitting `final` on promoted properties
below 8.5).

## Dependencies

| Package | Role |
| --- | --- |
| [`bradietilley/php-data`](https://bradietilley.dev/php-data) | Builder objects extend `Data` for typed construction |

Laravel is **not** required at runtime.

## Versioning

This package follows [Semantic Versioning](https://semver.org). Breaking changes
are reserved for major releases.

## Notes for contributors

The test suite, static analysis and code style all run in CI:

```bash
composer test          # Pest
composer analyse       # PHPStan
composer format        # Pint (autofix)
composer format:check  # Pint (check only)
```

All three must pass before a change is merged.
