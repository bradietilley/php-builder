<?php

uses(Tests\TestCase::class)->in('Feature');

if (! function_exists('sample_test_file')) {
    function sample_test_file(string $path = ''): string
    {
        return rtrim(__DIR__ . '/data/' . ltrim($path, '/'), '/');
    }
}
