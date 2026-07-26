<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Exceptions\InvalidPhpDefinitionException;
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
        public ?string $expression = null,
        public bool $stub = false,
        public bool $byRef = false,
    ) {
    }

    public function toPhp(int $indent = 0): string
    {
        $prefix = Indent::of($indent);
        $name = ($this->byRef ? '&' : '') . 'get';

        if ($this->stub) {
            if ($this->expression !== null || $this->lines !== []) {
                throw new InvalidPhpDefinitionException('Stub get hooks cannot have an expression or body lines.');
            }

            return $prefix . $name . ';';
        }

        if ($this->expression !== null) {
            if ($this->lines !== []) {
                throw new InvalidPhpDefinitionException('Expression get hooks cannot also have body lines.');
            }

            return $prefix . $name . ' => ' . $this->expression . ';';
        }

        $bodyPrefix = Indent::of($indent + 1);
        $out = [$prefix . $name . ' {'];

        foreach ($this->lines as $line) {
            $out[] = $line === '' ? '' : $bodyPrefix . $line;
        }

        $out[] = $prefix . '}';

        return implode("\n", $out);
    }
}
