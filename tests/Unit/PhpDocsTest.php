<?php

use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpTemplate;
use BradieTilley\Builder\Types\PhpGeneric;

test('class docs emit custom phpdoc lines such as property tags', function () {
    $class = new PhpClass(
        name: 'Post',
        namespace: 'App\\Models',
        description: 'A blog post',
        docs: [
            '@property int $id',
            '@property string $title',
            '@property-read \\App\\Models\\User $author',
        ],
    );

    expect($class->toPhp())->toContain(<<<'PHP'
/**
 * A blog post
 *
 * @property int $id
 * @property string $title
 * @property-read \App\Models\User $author
 */
class Post
PHP);
});

test('class docs appear after template tags', function () {
    $class = new PhpClass(
        name: 'Collection',
        description: 'Typed collection',
        templates: [new PhpTemplate(name: 'T')],
        docs: [
            '@implements \\IteratorAggregate<int, T>',
        ],
    );

    expect($class->toPhp())->toContain(<<<'PHP'
/**
 * Typed collection
 *
 * @template T
 * @implements \IteratorAggregate<int, T>
 */
class Collection
PHP);
});

test('method docs append custom lines after generated tags', function () {
    $method = new PhpMethod(
        name: 'find',
        args: [],
        return: PhpGeneric::for('Illuminate\\Support\\Collection', key: 'int', value: 'string'),
        lines: ['return collect();'],
        description: 'Find rows',
        docs: [
            '@see \\App\\Repositories\\PostRepository',
        ],
    );

    expect($method->toPhp())->toBe(<<<'PHP'
/**
 * Find rows
 *
 * @return Illuminate\Support\Collection<int, string>
 * @see \App\Repositories\PostRepository
 */
public function find(): Illuminate\Support\Collection
{
    return collect();
}
PHP);
});

test('property docs append custom lines', function () {
    $property = new PhpProperty(
        name: 'meta',
        type: PhpGeneric::array(value: 'mixed'),
        description: 'Arbitrary metadata',
        docs: [
            '@phpstan-var array<string, mixed>',
        ],
    );

    expect($property->toPhp())->toBe(<<<'PHP'
/**
 * Arbitrary metadata
 *
 * @var array<mixed> $meta
 * @phpstan-var array<string, mixed>
 */
public array $meta;
PHP);
});

test('docs alone without description still render a docblock', function () {
    $class = new PhpClass(
        name: 'Post',
        docs: ['@property string $title'],
    );

    expect($class->toPhp())->toContain(<<<'PHP'
/**
 * @property string $title
 */
class Post
PHP);
});
