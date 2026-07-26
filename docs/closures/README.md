# Closures

`PhpMethod::fromClosure()` turns a live PHP closure into a `PhpMethod` by reading
its source (via [`nikic/php-parser`](https://github.com/nikic/PHP-Parser)) and
copying the signature and body into builder form.

This is intended for **generate-time** authoring: define behaviour as closures in
a spec file, then emit real methods into generated classes. The generated PHP is
self-contained — there is no runtime registry that invokes the original closure.

```php
use BradieTilley\Builder\PhpMethod;
use DateTimeInterface;

$closure = function (?DateTimeInterface $date = null): void {
    $this->update([
        'published_at' => $date ?? now(),
    ]);
};

$method = PhpMethod::fromClosure(
    $closure,
    name: 'publish',
    description: 'Publish the model',
);

// public function publish(?DateTimeInterface $date = null): void
// {
//     $this->update(['published_at' => $date ?? now()]);
// }
```

## Rules

| Case | Behaviour |
| --- | --- |
| Long `function (…) { … }` | Body statements are pretty-printed into `$lines` |
| Arrow `fn (…) => …` | Becomes a single `return …;` line |
| `static function` | Sets `$static = true` on the method |
| Parameter / return types | Taken from reflection (resolved names) |
| Default values | Taken from the closure AST as source expressions |
| `use ($x)` bindings | **Rejected** — outer variables cannot exist on the generated method |
| Eval'd / no-file closures | Rejected |

`$this` inside a closure is fine: once inlined onto a class method, `$this` refers
to the instance of that class.

## Options

```php
PhpMethod::fromClosure(
    $closure,
    name: 'publish',
    visibility: PhpMethod::protected(), // or PhpVisibility::Protected
    final: true,
    description: '…',
    throws: ['RuntimeException'],
    attributes: [/* PhpAttribute… */],
);
```

Visibility / final / description / throws / attributes are chosen by the caller;
everything else (args, return, body, static) comes from the closure.
