<?php

namespace BradieTilley\Builder\Concerns;

use BradieTilley\Builder\PhpTraitAlias;
use BradieTilley\Builder\PhpTraitInsteadof;
use BradieTilley\Builder\PhpUseTrait;

trait ResolvesTraitUses
{
    protected function resolveTraitUse(PhpUseTrait $trait): PhpUseTrait
    {
        $resolved = clone $trait;
        $resolved->names = array_map(
            fn (string $name): string => $this->resolveTypeName($name) ?? $name,
            $trait->names,
        );

        $resolved->aliases = array_map(function (PhpTraitAlias $alias): PhpTraitAlias {
            $copy = clone $alias;

            if ($copy->trait !== null) {
                $copy->trait = $this->resolveTypeName($copy->trait) ?? $copy->trait;
            }

            return $copy;
        }, $trait->aliases);

        $resolved->insteadof = array_map(function (PhpTraitInsteadof $item): PhpTraitInsteadof {
            $copy = clone $item;
            $copy->from = $this->resolveTypeName($copy->from) ?? $copy->from;
            $copy->insteadOf = $this->resolveTypeName($copy->insteadOf) ?? $copy->insteadOf;

            return $copy;
        }, $trait->insteadof);

        return $resolved;
    }
}
