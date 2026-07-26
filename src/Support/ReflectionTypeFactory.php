<?php

namespace BradieTilley\Builder\Support;

use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Types\PhpIntersectionType;
use BradieTilley\Builder\Types\PhpNamedType;
use BradieTilley\Builder\Types\PhpUnionType;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class ReflectionTypeFactory
{
    public static function make(?ReflectionType $type): ?PhpType
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            return new PhpNamedType(
                name: $type->getName(),
                nullable: $type->allowsNull() && $type->getName() !== 'mixed' && $type->getName() !== 'null',
            );
        }

        if ($type instanceof ReflectionUnionType) {
            $types = [];
            $nullable = false;

            foreach ($type->getTypes() as $inner) {
                if ($inner instanceof ReflectionNamedType && $inner->getName() === 'null') {
                    $nullable = true;

                    continue;
                }

                $resolved = self::make($inner);

                if ($resolved !== null) {
                    $types[] = $resolved;
                }
            }

            return new PhpUnionType($types, nullable: $nullable);
        }

        if ($type instanceof ReflectionIntersectionType) {
            $types = [];

            foreach ($type->getTypes() as $inner) {
                $resolved = self::make($inner);

                if ($resolved !== null) {
                    $types[] = $resolved;
                }
            }

            return new PhpIntersectionType($types);
        }

        return null;
    }
}
