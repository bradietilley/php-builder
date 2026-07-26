<?php

namespace BradieTilley\Builder\Types;

use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Data\Data;

class PhpNamedType extends Data implements PhpType
{
    public function __construct(
        public string $name,
        public bool $nullable = false,
    ) {
        $this->name = match ($this->name) {
            'integer' => 'int',
            'boolean' => 'bool',
            'double' => 'float',
            default => $this->name,
        };
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function needsPhpDoc(): bool
    {
        return false;
    }

    public function withResolvedImports(ImportBag $imports): static
    {
        if (! str_contains(ltrim($this->name, '\\'), '\\')) {
            return $this;
        }

        $resolved = clone $this;
        $resolved->name = $imports->import($this->name);

        return $resolved;
    }

    public function toPhp(int $indent = 0): string
    {
        $name = $this->name;

        if ($this->nullable && $name !== 'mixed' && ! str_starts_with($name, '?')) {
            return '?' . $name;
        }

        return $name;
    }

    public function toPhpDoc(): string
    {
        if ($this->nullable && $this->name !== 'mixed' && ! str_contains($this->name, '|')) {
            return $this->name . '|null';
        }

        return $this->name;
    }
}
