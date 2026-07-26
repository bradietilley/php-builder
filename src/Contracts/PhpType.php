<?php

namespace BradieTilley\Builder\Contracts;

interface PhpType extends ExportsPhp, ExportsPhpDoc, ResolvesTypeImports
{
    public function isNullable(): bool;

    public function needsPhpDoc(): bool;
}
