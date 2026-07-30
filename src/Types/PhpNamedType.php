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
        $bare = ltrim($this->name, '\\');

        // Already absolute global (`\SplFileInfo`) or a language type — leave alone.
        if (! str_contains($bare, '\\')) {
            if (self::isLanguageType($bare) || str_starts_with($this->name, '\\')) {
                return $this;
            }

            // Unqualified non-language name from reflection (SplFileInfo, Closure).
            // Emit absolute so it won't resolve under the generated file's namespace.
            $resolved = clone $this;
            $resolved->name = '\\'.$bare;

            return $resolved;
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

    private static function isLanguageType(string $name): bool
    {
        return in_array($name, [
            'int', 'string', 'bool', 'float', 'array', 'object', 'mixed',
            'void', 'never', 'callable', 'iterable', 'false', 'true', 'null',
            'self', 'static', 'parent',
        ], true);
    }
}
