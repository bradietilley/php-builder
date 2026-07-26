# Formatting

Generated PHP is structurally valid but not run through an opinionated style
tool. Register a global formatter callback to pipe every full-file `toPhp()`
result through Pint, PHP-CS-Fixer, or your own fixer.

```php
use BradieTilley\Builder\PhpFormatter;

PhpFormatter::using(function (string $php): string {
    // run pint / php-cs-fixer / custom formatter
    return $php;
});
```

## API

```php
PhpFormatter::using(?callable $callback): void; // set or clear with null
PhpFormatter::clear(): void;                    // remove the callback
PhpFormatter::format(string $php): string;      // apply (or no-op)
```

The callback receives the complete file string and must return the (possibly
modified) string. Nested `toPhp($indent > 0)` snippets used as fragments do
**not** go through the file renderer, so they are not formatted by this hook —
only top-level file renders call `PhpFormatter::format()`.

## Example with a temp file + Pint

```php
use BradieTilley\Builder\PhpFormatter;

PhpFormatter::using(function (string $php): string {
    $path = tempnam(sys_get_temp_dir(), 'php-builder-');
    file_put_contents($path, $php);

    exec(escapeshellarg(base_path('vendor/bin/pint')) . ' ' . escapeshellarg($path));

    $formatted = file_get_contents($path);
    unlink($path);

    return $formatted === false ? $php : $formatted;
});
```

Clear the callback in tests (`PhpFormatter::clear()`) so formatting does not
leak across cases.

Continue to [PHP Target](../php-target/README.md).
