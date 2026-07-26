<?php

namespace BradieTilley\Builder\Types;

use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Data;

class PhpArrayType extends Data implements PhpType
{
    public PhpType $value;

    public ?PhpType $key;

    public function __construct(
        PhpType|string $value,
        PhpType|string|null $key = null,
        public bool $nullable = false,
    ) {
        $this->value = TypeFactory::makeRequired($value);
        $this->key = TypeFactory::make($key);
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function needsPhpDoc(): bool
    {
        return true;
    }

    public function toPhp(int $indent = 0): string
    {
        return $this->nullable ? '?array' : 'array';
    }

    public function toPhpDoc(): string
    {
        $inner = $this->key !== null
            ? $this->key->toPhpDoc() . ', ' . $this->value->toPhpDoc()
            : $this->value->toPhpDoc();

        $doc = 'array<' . $inner . '>';

        return $this->nullable ? $doc . '|null' : $doc;
    }
}
