# PHP Builder

A PHP code builder for programmatically generating classes, interfaces, traits,
and enums — with imports, attributes, typed members, property hooks, and PHPDoc.

![Static Analysis](https://github.com/bradietilley/php-builder/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/php-builder/actions/workflows/tests.yml/badge.svg)
![PHP Version](https://img.shields.io/badge/PHP%20Version-8.3-4F5B93)

## Documentation

Full documentation is available at [bradietilley.dev/php-builder](https://bradietilley.dev/php-builder).

## Installation

```bash
composer require bradietilley/php-builder
```

```php
use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\Types\PhpGeneric;

$class = new PhpClass(
    namespace: "App\Models",
    name: "Post",
    extends: "App\Models\Model",
    implements: "App\Models\Contracts\WithSlug",
    traits: [
        "App\Models\Concerns\HasSlug",
    ],
    constants: [],
    attributes: [],
    properties: [],
    methods: [],
);

$class->methods[] = new PhpMethod(
    visibility: PhpMethod::public(),
    final: true,
    name: 'setTags',
    args: [
        new PhpArgument(
            type: PhpGeneric::array(value: 'string'),
            name: 'tags',
            defaultValue: '[]',
        ),
    ],
    // FQCNs in type annotations are imported (and clash-aliased) at generate time.
    // extends already reserved "Model", so this becomes ModelEloquent.
    return: 'Illuminate\Database\Eloquent\Model',
    lines: [
        '$tags = Tag::query()->whereIn("name", $tags)->pluck("id");',
        '$this->tags()->sync($tags);',
        'return $this;',
    ],
    description: 'Set the tags',
);

$class->toPhp();
```

Generates:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Contracts\WithSlug;
use Illuminate\Database\Eloquent\Model as ModelEloquent;

class Post extends Model implements WithSlug
{
    use HasSlug;

    /**
     * Set the tags
     *
     * @param array<string> $tags
     */
    final public function setTags(array $tags = []): ModelEloquent
    {
        $tags = Tag::query()->whereIn("name", $tags)->pluck("id");
        $this->tags()->sync($tags);
        return $this;
    }
}
```

When you need the aliased name inside method body lines, import it explicitly first:

```php
// Automatic clash alias (extends already took "Model"):
$name = $class->import('Illuminate\Database\Eloquent\Model'); // "ModelEloquent"

// Or choose the alias yourself:
$name = $class->import('Illuminate\Database\Eloquent\Model', 'EloquentModel');

// use $name in return: and/or in $lines
```

### Post-generation formatting

Optionally register a callback to format full-file output (e.g. via Pint):

```php
use BradieTilley\Builder\PhpFormatter;

PhpFormatter::using(function (string $php): string {
    // run pint / php-cs-fixer / custom formatter
    return $php;
});
```

See the [documentation](https://bradietilley.dev/php-builder) for classes, interfaces, traits, enums, methods, properties & hooks, types, imports, attributes, formatting, and PHP targeting.

## Credits

- [Bradie Tilley](https://github.com/bradietilley)
