<?php

use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpAttribute;
use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\PhpClassConstant;
use BradieTilley\Builder\PhpFormatter;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpTraitAlias;
use BradieTilley\Builder\PhpTraitInsteadof;
use BradieTilley\Builder\PhpUseTrait;
use BradieTilley\Builder\Types\PhpArrayType;

afterEach(function () {
    PhpFormatter::clear();
});

test('class exports full file with imports traits constants properties and methods', function () {
    $class = new PhpClass(
        namespace: 'App\\Models',
        name: 'Post',
        extends: 'App\\Models\\Model',
        implements: 'App\\Models\\Contracts\\WithSlug',
        traits: [
            'App\\Models\\Concerns\\HasSlug',
        ],
        constants: [
            new PhpClassConstant(name: 'TYPE', value: "'post'", type: 'string'),
        ],
        attributes: [
            new PhpAttribute('AllowDynamicProperties'),
        ],
        properties: [
            new PhpProperty(type: 'string', name: 'title'),
        ],
        methods: [
            new PhpMethod(
                name: 'getTitle',
                return: 'string',
                lines: ['return $this->title;'],
            ),
        ],
    );

    expect($class->toPhp())->toBe(<<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Contracts\WithSlug;

#[AllowDynamicProperties]
class Post extends Model implements WithSlug
{
    use HasSlug;

    public const string TYPE = 'post';

    public string $title;

    public function getTitle(): string
    {
        return $this->title;
    }
}

PHP);
});

test('readme golden example', function () {
    $class = new PhpClass(
        namespace: 'App\\Models',
        name: 'Post',
        extends: 'App\\Models\\Model',
        implements: 'App\\Models\\Contracts\\WithSlug',
        traits: [
            'App\\Models\\Concerns\\HasSlug',
        ],
    );

    $class->methods[] = new PhpMethod(
        visibility: PhpMethod::VISIBILITY_PUBLIC,
        final: true,
        name: 'setTags',
        args: [
            new PhpArgument(
                type: new PhpArrayType(value: 'string'),
                name: 'tags',
                defaultValue: '[]',
            ),
        ],
        return: 'Illuminate\\Database\\Eloquent\\Model',
        lines: [
            '$tags = Tag::query()->whereIn("name", $tags)->pluck("id");',
            '$this->tags()->sync($tags);',
            'return $this;',
        ],
        description: 'Set the tags',
    );

    expect($class->toPhp())->toBe(<<<'PHP'
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

PHP);
});

test('return type fqcn is auto-imported with clash alias at generate time', function () {
    $class = new PhpClass(
        namespace: 'App\\Models',
        name: 'Post',
        extends: 'App\\Models\\Model',
        methods: [
            new PhpMethod(
                name: 'asEloquent',
                return: 'Illuminate\\Database\\Eloquent\\Model',
                lines: ['return $this;'],
            ),
        ],
        strictTypes: false,
    );

    $php = $class->toPhp();

    expect($php)->toContain('use Illuminate\\Database\\Eloquent\\Model as ModelEloquent;')
        ->and($php)->toContain('function asEloquent(): ModelEloquent')
        ->and($php)->toContain('class Post extends Model');
});

test('nested array type fqcn is auto-imported for phpdoc', function () {
    $class = new PhpClass(
        namespace: 'App\\Models',
        name: 'Post',
        methods: [
            new PhpMethod(
                name: 'tags',
                args: [
                    new PhpArgument(
                        type: new PhpArrayType(value: 'Illuminate\\Support\\Collection'),
                        name: 'tags',
                    ),
                ],
                lines: ['return $tags;'],
            ),
        ],
        strictTypes: false,
    );

    $php = $class->toPhp();

    expect($php)->toContain('use Illuminate\\Support\\Collection;')
        ->and($php)->toContain('@param array<Collection> $tags')
        ->and($php)->toContain('function tags(array $tags)');
});

test('explicit import alias still works for return type', function () {
    $class = new PhpClass(
        namespace: 'App\\Models',
        name: 'Post',
        extends: 'App\\Models\\Model',
        strictTypes: false,
    );

    $name = $class->import('Illuminate\\Database\\Eloquent\\Model');

    $class->methods[] = new PhpMethod(
        name: 'asEloquent',
        return: $name,
        lines: ['return $this;'],
    );

    expect($name)->toBe('ModelEloquent')
        ->and($class->toPhp())->toContain('function asEloquent(): ModelEloquent');
});

test('same fqcn in return and param resolves once', function () {
    $class = new PhpClass(
        namespace: 'App',
        name: 'Example',
        methods: [
            new PhpMethod(
                name: 'wrap',
                args: [
                    new PhpArgument(type: 'Illuminate\\Support\\Collection', name: 'items'),
                ],
                return: 'Illuminate\\Support\\Collection',
                lines: ['return $items;'],
            ),
        ],
        strictTypes: false,
    );

    $php = $class->toPhp();

    expect(substr_count($php, 'use Illuminate\\Support\\Collection;'))->toBe(1)
        ->and($php)->toContain('function wrap(Collection $items): Collection');
});

test('short type names are left unchanged', function () {
    $class = new PhpClass(
        namespace: 'App',
        name: 'Example',
        methods: [
            new PhpMethod(
                name: 'id',
                return: 'string',
                lines: ['return "1";'],
            ),
        ],
        strictTypes: false,
    );

    expect($class->toPhp())->toContain('function id(): string')
        ->and($class->toPhp())->not->toContain('use ');
});

test('trait aliases are exported', function () {
    $class = new PhpClass(
        namespace: 'App',
        name: 'Example',
        traits: [
            new PhpUseTrait(
                name: 'App\\Concerns\\HasSlug',
                aliases: ['bootHasSlug' => 'bootSlug'],
            ),
        ],
        strictTypes: false,
    );

    expect($class->toPhp())->toContain("use HasSlug {\n        bootHasSlug as bootSlug;\n    }");
});

test('multi-trait use supports insteadof and visibility aliases', function () {
    $class = new PhpClass(
        namespace: 'App',
        name: 'Example',
        traits: [
            new PhpUseTrait(
                name: ['App\\A', 'App\\B'],
                aliases: [
                    new PhpTraitAlias(
                        method: 'boot',
                        alias: 'bootA',
                        visibility: PhpTraitAlias::VISIBILITY_PRIVATE,
                        trait: 'App\\A',
                    ),
                ],
                insteadof: [
                    new PhpTraitInsteadof(
                        method: 'boot',
                        from: 'App\\A',
                        insteadOf: 'App\\B',
                    ),
                ],
            ),
        ],
        strictTypes: false,
    );

    expect($class->toPhp())->toContain("use A, B {\n        A::boot insteadof B;\n        A::boot as private bootA;\n    }");
});

test('class description exports as type phpdoc', function () {
    $class = new PhpClass(
        namespace: 'App',
        name: 'Post',
        description: 'A blog post model',
        strictTypes: false,
    );

    expect($class->toPhp())->toContain("/**\n * A blog post model\n */\nclass Post");
});

test('formatter callback is applied to full file export only', function () {
    PhpFormatter::using(fn (string $php): string => str_replace('Example', 'FORMATTED', $php));

    $class = new PhpClass(namespace: 'App', name: 'Example', strictTypes: false);
    $method = new PhpMethod(name: 'example', lines: ['//']);

    expect($class->toPhp())->toContain('class FORMATTED')
        ->and($method->toPhp())->toContain('function example')
        ->and($method->toPhp())->not->toContain('FORMATTED');
});

test('class can be hydrated from array', function () {
    $class = PhpClass::from([
        'namespace' => 'App',
        'name' => 'Demo',
        'strictTypes' => false,
        'methods' => [
            [
                'name' => 'ping',
                'lines' => ['return true;'],
                'return' => 'bool',
            ],
        ],
    ]);

    expect($class->toPhp())->toContain('function ping(): bool')
        ->and($class->toPhp())->toContain('return true;');
});
