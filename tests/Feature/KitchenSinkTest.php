<?php

require_once __DIR__ . '/../Support/KitchenSinkBuilders.php';

test('kitchen sink php class matches expected fixture', function () {
    expect(kitchenSinkClass()->toPhp())
        ->toBe(file_get_contents(sample_test_file('expected-php-class.txt')));
});

test('kitchen sink php interface matches expected fixture', function () {
    expect(kitchenSinkInterface()->toPhp())
        ->toBe(file_get_contents(sample_test_file('expected-php-interface.txt')));
});

test('kitchen sink php enum matches expected fixture', function () {
    expect(kitchenSinkEnum()->toPhp())
        ->toBe(file_get_contents(sample_test_file('expected-php-enum.txt')));
});

test('kitchen sink pure php enum matches expected fixture', function () {
    expect(kitchenSinkPureEnum()->toPhp())
        ->toBe(file_get_contents(sample_test_file('expected-php-pure-enum.txt')));
});

test('kitchen sink php trait matches expected fixture', function () {
    expect(kitchenSinkTrait()->toPhp())
        ->toBe(file_get_contents(sample_test_file('expected-php-trait.txt')));
});
