<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Concerns\HasVisibility;
use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Data;

class PhpTraitAlias extends Data implements ExportsPhp
{
    use HasVisibility;

    public function __construct(
        public string $method,
        public ?string $alias = null,
        public ?PhpVisibility $visibility = null,
        public ?string $trait = null,
    ) {
    }

    public function toPhp(int $indent = 0): string
    {
        $left = $this->trait !== null
            ? $this->trait . '::' . $this->method
            : $this->method;

        $right = trim(($this->visibility->value ?? '') . ' ' . ($this->alias ?? ''));

        if ($right === '') {
            $right = $this->method;
        }

        return Indent::of($indent) . $left . ' as ' . $right . ';';
    }
}
