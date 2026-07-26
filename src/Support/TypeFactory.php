<?php

namespace BradieTilley\Builder\Support;

use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Types\PhpIntersectionType;
use BradieTilley\Builder\Types\PhpNamedType;
use BradieTilley\Builder\Types\PhpUnionType;

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

        $type = trim($type);

        if ($type === '') {
            return null;
        }

        // Split unions/intersections only when the string has no generics, so
        // values like `array<string|int>` stay as a single named type.
        if (! str_contains($type, '<')) {
            if (str_contains($type, '|')) {
                return new PhpUnionType(
                    array_map(trim(...), explode('|', $type)),
                );
            }

            if (str_contains($type, '&')) {
                return new PhpIntersectionType(
                    array_map(trim(...), explode('&', $type)),
                );
            }
        }

        if (str_starts_with($type, '?')) {
            return new PhpNamedType(substr($type, 1), nullable: true);
        }

        return new PhpNamedType($type);
    }

    public static function makeRequired(PhpType|string $type): PhpType
    {
        return $type instanceof PhpType ? $type : self::make($type) ?? new PhpNamedType($type);
    }
}
