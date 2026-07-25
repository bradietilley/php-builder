<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Data;

class PhpUseTrait extends Data implements ExportsPhp
{
    /**
     * @param  array<string, string>  $aliases  method => alias (use A { foo as bar; })
     * @param  array<string, string>  $insteadof  method => conflicting trait name
     */
    public function __construct(
        public string $name,
        public array $aliases = [],
        public array $insteadof = [],
    ) {
    }

    public function toPhp(int $indent = 0): string
    {
        $prefix = Indent::of($indent);

        if ($this->aliases === [] && $this->insteadof === []) {
            return $prefix.'use '.$this->name.';';
        }

        $lines = [$prefix.'use '.$this->name.' {'];
        $inner = Indent::of($indent + 1);

        foreach ($this->insteadof as $method => $otherTrait) {
            $lines[] = $inner.$this->name.'::'.$method.' insteadof '.$otherTrait.';';
        }

        foreach ($this->aliases as $method => $alias) {
            $lines[] = $inner.$method.' as '.$alias.';';
        }

        $lines[] = $prefix.'}';

        return implode("\n", $lines);
    }
}
