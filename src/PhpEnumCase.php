<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpEnumCase extends Data implements ExportsPhp
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
