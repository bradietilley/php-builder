<?php

namespace BradieTilley\Builder\Types;

use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Data;

class PhpGeneric extends Data implements PhpType
{
    public PhpType $value;

    public ?PhpType $key;

    public function __construct(
        public string $name,
        PhpType|string $value,
        PhpType|string|null $key = null,
        public bool $nullable = false,
    ) {
        $this->value = TypeFactory::makeRequired($value);
        $this->key = TypeFactory::make($key);
    }

    public static function for(
        string $name,
        PhpType|string $value,
        PhpType|string|null $key = null,
        bool $nullable = false,
    ): self {
        return new self(name: $name, value: $value, key: $key, nullable: $nullable);
    }

    public static function array(
        PhpType|string $value,
        PhpType|string|null $key = null,
        bool $nullable = false,
    ): self {
        return self::for('array', value: $value, key: $key, nullable: $nullable);
    }

    public static function list(
        PhpType|string $value,
        bool $nullable = false,
    ): self {
        return self::for('list', value: $value, nullable: $nullable);
    }

    public static function iterable(
        PhpType|string $value,
        PhpType|string|null $key = null,
        bool $nullable = false,
    ): self {
        return self::for('iterable', value: $value, key: $key, nullable: $nullable);
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function needsPhpDoc(): bool
    {
        return true;
    }

    public function withResolvedImports(ImportBag $imports): static
    {
        $resolved = clone $this;

        if (str_contains(ltrim($this->name, '\\'), '\\')) {
            $resolved->name = $imports->import($this->name);
        }

        $resolved->value = $this->value->withResolvedImports($imports);
        $resolved->key = $this->key?->withResolvedImports($imports);

        return $resolved;
    }

    public function toPhp(int $indent = 0): string
    {
        $name = $this->name === 'list' ? 'array' : $this->name;

        return $this->nullable ? '?' . $name : $name;
    }

    public function toPhpDoc(): string
    {
        $inner = $this->key !== null
            ? $this->key->toPhpDoc() . ', ' . $this->value->toPhpDoc()
            : $this->value->toPhpDoc();

        $doc = $this->name . '<' . $inner . '>';

        return $this->nullable ? $doc . '|null' : $doc;
    }
}
