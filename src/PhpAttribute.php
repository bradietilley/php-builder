<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpAttribute extends Data implements ExportsPhp
{
    /**
     * @param  list<string>  $arguments  Raw PHP expression strings
     */
    public function __construct(
        public string $name,
        #[ArrayOf('string')]
        public array $arguments = [],
    ) {
    }

    public function toPhp(int $indent = 0): string
    {
        $args = $this->arguments === []
            ? ''
            : '(' . implode(', ', $this->arguments) . ')';

        return Indent::of($indent) . '#[' . $this->name . $args . ']';
    }
}
