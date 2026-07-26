<?php

namespace BradieTilley\Builder\Support;

/**
 * Raw PHP expression embedded by {@see PhpValue::export()} without quoting.
 */
final readonly class PhpExpression
{
    public function __construct(public string $code)
    {
    }
}
