<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Contracts\ResolvesTypeImports;
use BradieTilley\Builder\Exceptions\InvalidPhpDefinitionException;
use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpPropertySetHook extends Data implements ExportsPhp, ResolvesTypeImports
{
    public ?PhpType $type;

    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        PhpType|string|null $type = null,
        public string $name = 'value',
        #[ArrayOf('string')]
        public array $lines = [],
        public ?string $expression = null,
        public bool $stub = false,
    ) {
        $this->type = TypeFactory::make($type);
    }

    public function withResolvedImports(ImportBag $imports): static
    {
        $resolved = clone $this;
        $resolved->type = $this->type?->withResolvedImports($imports);

        return $resolved;
    }

    public function toPhp(int $indent = 0): string
    {
        $prefix = Indent::of($indent);

        if ($this->stub) {
            if ($this->expression !== null || $this->lines !== [] || $this->type !== null) {
                throw new InvalidPhpDefinitionException('Stub set hooks cannot have a type, expression, or body lines.');
            }

            return $prefix . 'set;';
        }

        if ($this->type === null) {
            throw new InvalidPhpDefinitionException('Set hooks require a parameter type unless they are stubs.');
        }

        $param = $this->type->toPhp() . ' $' . $this->name;

        if ($this->expression !== null) {
            if ($this->lines !== []) {
                throw new InvalidPhpDefinitionException('Expression set hooks cannot also have body lines.');
            }

            return $prefix . 'set(' . $param . ') => ' . $this->expression . ';';
        }

        $bodyPrefix = Indent::of($indent + 1);
        $out = [$prefix . 'set(' . $param . ') {'];

        foreach ($this->lines as $line) {
            $out[] = $line === '' ? '' : $bodyPrefix . $line;
        }

        $out[] = $prefix . '}';

        return implode("\n", $out);
    }
}
