<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Data;

class PhpTraitInsteadof extends Data implements ExportsPhp
{
    public function __construct(
        public string $method,
        public string $from,
        public string $insteadOf,
    ) {
    }

    public function toPhp(int $indent = 0): string
    {
        return Indent::of($indent).$this->from.'::'.$this->method.' insteadof '.$this->insteadOf.';';
    }
}
