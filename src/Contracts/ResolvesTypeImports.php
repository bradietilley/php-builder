<?php

namespace BradieTilley\Builder\Contracts;

use BradieTilley\Builder\Support\ImportBag;

interface ResolvesTypeImports
{
    public function withResolvedImports(ImportBag $imports): static;
}
