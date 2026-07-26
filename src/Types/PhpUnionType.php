<?php

namespace BradieTilley\Builder\Types;

use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Data;

class PhpUnionType extends Data implements PhpType
{
    /** @var list<PhpType> */
    public array $types = [];

    /**
     * @param  list<PhpType|string>  $types
     */
    public function __construct(
        array $types,
        public bool $nullable = false,
    ) {
        foreach ($types as $type) {
            $resolved = TypeFactory::make($type);

            if ($resolved !== null) {
                $this->types[] = $resolved;
            }
        }
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function needsPhpDoc(): bool
    {
        foreach ($this->types as $type) {
            if ($type->needsPhpDoc()) {
                return true;
            }
        }

        return false;
    }

    public function withResolvedImports(ImportBag $imports): static
    {
        $resolved = clone $this;
        $resolved->types = array_map(
            fn (PhpType $type): PhpType => $type->withResolvedImports($imports),
            $this->types,
        );

        return $resolved;
    }

    public function toPhp(int $indent = 0): string
    {
        $parts = array_map(fn (PhpType $type): string => $type->toPhp(), $this->types);

        if ($this->nullable && ! in_array('null', $parts, true) && ! in_array('mixed', $parts, true)) {
            $parts[] = 'null';
        }

        return implode('|', $parts);
    }

    public function toPhpDoc(): string
    {
        $parts = array_map(fn (PhpType $type): string => $type->toPhpDoc(), $this->types);

        if ($this->nullable && ! in_array('null', $parts, true) && ! str_contains(implode('|', $parts), 'null')) {
            $parts[] = 'null';
        }

        return implode('|', $parts);
    }
}
