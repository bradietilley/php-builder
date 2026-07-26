<?php

use BradieTilley\Builder\Support\PhpFeature;
use BradieTilley\Builder\Support\PhpTarget;

afterEach(function () {
    PhpTarget::clear();
});

test('default target is package minimum 8.3', function () {
    expect(PhpTarget::version())->toBe('8.3.0')
        ->and(PhpTarget::supports(PhpFeature::FinalPromotedProperties))->toBeFalse();
});

test('using normalizes short versions', function () {
    PhpTarget::using('8.5');

    expect(PhpTarget::version())->toBe('8.5.0')
        ->and(PhpTarget::supports(PhpFeature::FinalPromotedProperties))->toBeTrue();
});

test('using null clears back to default', function () {
    PhpTarget::using('8.5');
    PhpTarget::using(null);

    expect(PhpTarget::version())->toBe('8.3.0');
});

test('current returns normalized host php version', function () {
    expect(PhpTarget::current())->toMatch('/^\d+\.\d+\.\d+$/');
});
