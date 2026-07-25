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

    $name = $class->import('Illuminate\\Database\\Eloquent\\Model');

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
        return: $name,
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
