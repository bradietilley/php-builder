<?php

use BradieTilley\Builder\Support\PhpExpression;
use BradieTilley\Builder\Support\PhpValue;

test('exports scalars with var_export-compatible literals', function () {
    expect(PhpValue::export(null))->toBe('null')
        ->and(PhpValue::export(true))->toBe('true')
        ->and(PhpValue::export(false))->toBe('false')
        ->and(PhpValue::export(15))->toBe('15')
        ->and(PhpValue::export('App\\Models\\Post'))->toBe("'App\\\\Models\\\\Post'");
});

test('exports arrays with short syntax and omits list keys', function () {
    expect(PhpValue::export([]))->toBe('[]');

    expect(PhpValue::export([
        'action' => 'index',
        'filters' => [
            [
                'name' => 'title',
                'column' => 'title',
            ],
        ],
        'include' => [],
        'paginate' => 15,
        'cursor' => false,
    ]))->toBe(<<<'PHP'
[
    'action' => 'index',
    'filters' => [
        [
            'name' => 'title',
            'column' => 'title',
        ],
    ],
    'include' => [],
    'paginate' => 15,
    'cursor' => false,
]
PHP);
});

test('embeds raw php expressions without quoting', function () {
    expect(PhpValue::export([
        'constraints' => new PhpExpression('static::class'),
    ]))->toBe(<<<'PHP'
[
    'constraints' => static::class,
]
PHP);
});

test('exports compact single-line arrays for inline embedding', function () {
    expect(PhpValue::exportInline(['mail', 'database']))->toBe("['mail', 'database']")
        ->and(PhpValue::exportInline(['profile' => true]))->toBe("['profile' => true]")
        ->and(PhpValue::exportInline([]))->toBe('[]');
});
