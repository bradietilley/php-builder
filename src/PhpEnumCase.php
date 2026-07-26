<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Contracts\ResolvesTypeImports;
use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpEnumCase extends Data implements ExportsPhp, ResolvesTypeImports
{
    /**
     * @param  list<PhpAttribute>  $attributes
     */
    public function __construct(
        public string $name,
        public string|int|null $value = null,
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
    ) {
    }

    public function withResolvedImports(ImportBag $imports): static
    {
        $resolved = clone $this;
        $resolved->attributes = array_map(
            fn (PhpAttribute $attribute): PhpAttribute => $attribute->withResolvedImports($imports),
            $this->attributes,
        );

        return $resolved;
    }

    public function toPhp(int $indent = 0): string
    {
        $prefix = Indent::of($indent);
        $lines = [];

        foreach ($this->attributes as $attribute) {
            $lines[] = $attribute->toPhp($indent);
        }

        $line = 'case ' . $this->name;

        if ($this->value !== null) {
            $line .= ' = ' . $this->value;
        }

        $lines[] = $prefix . $line . ';';

        return implode("\n", $lines);
    }
}
