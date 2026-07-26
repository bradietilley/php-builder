# PHP Builder

A fluent, object-oriented PHP code builder for programmatically generating
classes, interfaces, traits, and enums — complete with imports, attributes,
typed members, property hooks, and PHPDoc.

```php
use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\Types\PhpArrayType;

$class = new PhpClass(
    namespace: 'App\\Models',
    name: 'Post',
    extends: 'App\\Models\\Model',
);

$class->methods[] = new PhpMethod(
    name: 'setTags',
    args: [
        new PhpArgument(
            type: new PhpArrayType(value: 'string'),
            name: 'tags',
            defaultValue: '[]',
        ),
    ],
    return: 'static',
    lines: [
        '$this->tags()->sync($tags);',
        'return $this;',
    ],
);

echo $class->toPhp();
```

## Documentation

- [Introduction](introduction/README.md)
- [Installation](installation/README.md)
- [Classes](classes/README.md)
- [Interfaces](interfaces/README.md)
- [Traits](traits/README.md)
- [Enums](enums/README.md)
- [Methods & Arguments](methods/README.md)
- [Properties & Hooks](properties/README.md)
- [Types](types/README.md)
- [Imports & Aliasing](imports/README.md)
- [Attributes & Constants](attributes/README.md)
- [Formatting](formatting/README.md)
- [PHP Target](php-target/README.md)
- [Compatibility](compatibility/README.md)

## Requirements

- PHP 8.3+
- [`bradietilley/php-data`](https://bradietilley.dev/php-data) (pulled in automatically)

## Quick start

```bash
composer require bradietilley/php-builder
```

```php
use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\PhpMethod;

$class = new PhpClass(
    namespace: 'App\\Support',
    name: 'Greeter',
    methods: [
        new PhpMethod(
            name: 'hello',
            return: 'string',
            lines: ["return 'hello';"],
        ),
    ],
);

file_put_contents('Greeter.php', $class->toPhp());
```

See [Installation](installation/README.md) and [Classes](classes/README.md) for the full walkthrough.
