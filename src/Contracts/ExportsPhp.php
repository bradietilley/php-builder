<?php

namespace BradieTilley\Builder\Contracts;

interface ExportsPhp
{
    public function toPhp(int $indent = 0): string;
}
