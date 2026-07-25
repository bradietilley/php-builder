<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpPropertySetHook extends Data implements ExportsPhp
{
    public PhpType $type;

    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        PhpType|string $type,
        public string $name = 'value',
        #[ArrayOf('string')]
        public array $lines = [],
    ) {
        $this->type = TypeFactory::makeRequired($type);
    }

    public function toPhp(int $indent = 0): string
    {
        $prefix = Indent::of($indent);
        $bodyPrefix = Indent::of($indent + 1);
        $out = [$prefix.'set('.$this->type->toPhp().' $'.$this->name.') {'];

        foreach ($this->lines as $line) {
            $out[] = $line === '' ? '' : $bodyPrefix.$line;
        }

        $out[] = $prefix.'}';

        return implode("\n", $out);
    }
}
