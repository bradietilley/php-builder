<?php

namespace BradieTilley\Builder\Support;

use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Types\PhpNamedType;

class TypeFactory
{
    public static function make(PhpType|string|null $type): ?PhpType
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof PhpType) {
            return $type;
        }

        return new PhpNamedType($type);
    }

    public static function makeRequired(PhpType|string $type): PhpType
    {
        return $type instanceof PhpType ? $type : new PhpNamedType($type);
    }
}
