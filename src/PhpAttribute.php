<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Contracts\ResolvesTypeImports;
use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpAttribute extends Data implements ExportsPhp, ResolvesTypeImports
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

    public function withResolvedImports(ImportBag $imports): static
    {
        if (! str_contains(ltrim($this->name, '\\'), '\\')) {
            return $this;
        }

        $resolved = clone $this;
        $resolved->name = $imports->import($this->name);

        return $resolved;
    }

    public function toPhp(int $indent = 0): string
    {
        $args = $this->arguments === []
            ? ''
            : '(' . implode(', ', $this->arguments) . ')';

        return Indent::of($indent) . '#[' . $this->name . $args . ']';
    }
}
