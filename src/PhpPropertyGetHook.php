<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpPropertyGetHook extends Data implements ExportsPhp
{
    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        #[ArrayOf('string')]
        public array $lines = [],
    ) {
    }

    public function toPhp(int $indent = 0): string
    {
        $prefix = Indent::of($indent);
        $bodyPrefix = Indent::of($indent + 1);
        $out = [$prefix.'get {'];

        foreach ($this->lines as $line) {
            $out[] = $line === '' ? '' : $bodyPrefix.$line;
        }

        $out[] = $prefix.'}';

        return implode("\n", $out);
    }
}
