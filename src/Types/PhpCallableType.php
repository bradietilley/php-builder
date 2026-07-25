<?php

namespace BradieTilley\Builder\Types;

use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Data;

class PhpCallableType extends Data implements PhpType
{
    /** @var list<PhpType> */
    public array $parameters = [];

    public ?PhpType $return = null;

    /**
     * @param  list<PhpType|string>  $parameters
     */
    public function __construct(
        array $parameters = [],
        PhpType|string|null $return = null,
        public bool $nullable = false,
        public bool $useClosure = false,
    ) {
        foreach ($parameters as $parameter) {
            $this->parameters[] = TypeFactory::makeRequired($parameter);
        }

        $this->return = TypeFactory::make($return);
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function needsPhpDoc(): bool
    {
        return $this->parameters !== [] || $this->return !== null;
    }

    public function toPhp(int $indent = 0): string
    {
        $name = $this->useClosure ? 'Closure' : 'callable';

        return $this->nullable ? '?'.$name : $name;
    }

    public function toPhpDoc(): string
    {
        if (! $this->needsPhpDoc()) {
            return $this->toPhp();
        }

        $params = implode(', ', array_map(
            fn (PhpType $type): string => $type->toPhpDoc(),
            $this->parameters,
        ));

        $doc = 'callable('.$params.')';

        if ($this->return !== null) {
            $doc .= ': '.$this->return->toPhpDoc();
        }

        return $this->nullable ? $doc.'|null' : $doc;
    }
}
