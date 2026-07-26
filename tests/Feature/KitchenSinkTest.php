<?php

use BradieTilley\Builder\Support\PhpTarget;

require_once __DIR__ . '/../Support/KitchenSinkBuilders.php';

beforeEach(function () {
    PhpTarget::using('8.3');
});

afterEach(function () {
    PhpTarget::clear();
});

/**
 * @return array<string, callable(): object>
 */
function kitchenSinkCases(): array
{
    return [
        'expected-php-class.txt' => fn () => kitchenSinkClass(),
        'expected-php-interface.txt' => fn () => kitchenSinkInterface(),
        'expected-php-enum.txt' => fn () => kitchenSinkEnum(),
        'expected-php-pure-enum.txt' => fn () => kitchenSinkPureEnum(),
        'expected-php-trait.txt' => fn () => kitchenSinkTrait(),
    ];
}

foreach (kitchenSinkCases() as $fixture => $factory) {
    test("kitchen sink {$fixture} matches expected fixture and passes php -l", function () use ($fixture, $factory) {
        $path = sample_test_file($fixture);
        $php = $factory()->toPhp();

        expect($php)->toBe(file_get_contents($path));

        $lint = [];
        $code = 0;
        exec('php -l ' . escapeshellarg($path) . ' 2>&1', $lint, $code);

        expect($code)->toBe(0, implode("\n", $lint));
    });
}
