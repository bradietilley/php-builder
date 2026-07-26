<?php

use BradieTilley\Builder\Exceptions\InvalidPhpDefinitionException;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\PhpVisibility;

test('fromClosure inlines a long closure into a method', function () {
    $closure = function (?\DateTimeInterface $date = null): void {
        $this->update([
            'published_at' => $date ?? now(),
        ]);
    };

    $method = PhpMethod::fromClosure(
        $closure,
        name: 'publish',
        description: 'Publish the model',
    );

    expect($method->toPhp())->toBe(<<<'PHP'
/**
 * Publish the model
 */
public function publish(?DateTimeInterface $date = null): void
{
    $this->update(['published_at' => $date ?? now()]);
}
PHP);
});

test('fromClosure inlines an arrow function as a return statement', function () {
    $closure = fn (int $n): int => $n * 2;

    $method = PhpMethod::fromClosure($closure, name: 'double');

    expect($method->toPhp())->toBe(<<<'PHP'
public function double(int $n): int
{
    return $n * 2;
}
PHP);
});

test('fromClosure preserves static and visibility', function () {
    $closure = static function (string $name): string {
        return strtoupper($name);
    };

    $method = PhpMethod::fromClosure(
        $closure,
        name: 'upper',
        visibility: PhpVisibility::Protected,
    );

    expect($method->static)->toBeTrue()
        ->and($method->visibility)->toBe(PhpVisibility::Protected)
        ->and($method->toPhp())->toBe(<<<'PHP'
protected static function upper(string $name): string
{
    return strtoupper($name);
}
PHP);
});

test('fromClosure rejects use bindings', function () {
    $prefix = 'x';
    $closure = function () use ($prefix): string {
        return $prefix;
    };

    PhpMethod::fromClosure($closure, name: 'fail');
})->throws(InvalidPhpDefinitionException::class, 'use()');

test('fromClosure prefers outer closure over nested arrow functions', function () {
    $closure = function (object $post, object $actor): void {
        $ids = [1, 2];

        collect($ids)->each(
            fn (int $id) => $actor->notify($post, $id),
        );
    };

    $method = PhpMethod::fromClosure($closure, name: 'handle');

    expect($method->toPhp())->toContain('collect($ids)->each(')
        ->and($method->toPhp())->toContain('fn(int $id) => $actor->notify($post, $id)')
        ->and($method->toPhp())->not->toBe(<<<'PHP'
public function handle(object $post, object $actor): void
{
    return $actor->notify($post, $id);
}
PHP);
});

test('fromClosure supports multi-statement bodies', function () {
    $closure = function (array $tags): self {
        $ids = Tag::query()->whereIn('name', $tags)->pluck('id');
        $this->tags()->sync($ids);

        return $this;
    };

    $method = PhpMethod::fromClosure($closure, name: 'setTags');

    expect($method->lines)->toBe([
        '$ids = Tag::query()->whereIn(\'name\', $tags)->pluck(\'id\');',
        '$this->tags()->sync($ids);',
        'return $this;',
    ]);
});
