# Compatibility

## Supported versions

| PHP | Status |
| --- | --- |
| 8.5 | Supported |

This package line requires PHP 8.5. Emitters always produce PHP 8.5 syntax
(for example, `final` on promoted constructor properties).

## Dependencies

| Package | Role |
| --- | --- |
| [`bradietilley/php-data`](https://bradietilley.dev/php-data) | Builder objects extend `Data` for typed construction |

Laravel is **not** required at runtime.

## Versioning

Package majors track PHP minors:

| Package | PHP |
| --- | --- |
| `v1.x` | 8.5 |
| `v2.x` | 8.6 |
| … | … |

Within a major, this package follows [Semantic Versioning](https://semver.org).
Breaking API changes are reserved for major releases (which also move to the
next PHP minor).

## Notes for contributors

The test suite, static analysis and code style all run in CI:

```bash
composer test          # Pest
composer analyse       # PHPStan
composer format        # Pint (autofix)
composer format:check  # Pint (check only)
```

All three must pass before a change is merged.
