<?php

namespace BradieTilley\Builder\Support;

class ImportBag
{
    /**
     * @var array<string, string|null> FQCN => alias (null means use basename)
     */
    protected array $imports = [];

    /**
     * @var array<string, string> usable name => FQCN
     */
    protected array $usedNames = [];

    public function __construct(
        public readonly string $namespace = '',
    ) {
    }

    /**
     * Reserve a name (e.g. from extends/implements) so later imports can clash-detect.
     */
    public function reserve(string $fqcn): string
    {
        return $this->import($fqcn);
    }

    /**
     * Register an FQCN import and return the usable short or aliased name.
     */
    public function import(string $fqcn): string
    {
        $fqcn = ltrim($fqcn, '\\');

        if ($fqcn === '') {
            return $fqcn;
        }

        if (! str_contains($fqcn, '\\')) {
            $this->usedNames[$fqcn] ??= $fqcn;

            return $fqcn;
        }

        if (array_key_exists($fqcn, $this->imports)) {
            return $this->imports[$fqcn] ?? self::basename($fqcn);
        }

        if ($this->isSameNamespaceSibling($fqcn)) {
            $base = self::basename($fqcn);

            if (! isset($this->usedNames[$base]) || $this->usedNames[$base] === $fqcn) {
                $this->usedNames[$base] = $fqcn;

                return $base;
            }
        }

        $alias = $this->resolveAlias($fqcn);
        $this->imports[$fqcn] = $alias === self::basename($fqcn) ? null : $alias;
        $this->usedNames[$alias] = $fqcn;

        return $alias;
    }

    /**
     * @return list<string>
     */
    public function toUseLines(): array
    {
        $lines = [];

        foreach ($this->sortedImports() as $fqcn => $alias) {
            $lines[] = $alias === null
                ? "use {$fqcn};"
                : "use {$fqcn} as {$alias};";
        }

        return $lines;
    }

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return $this->sortedImports();
    }

    public static function basename(string $fqcn): string
    {
        $fqcn = ltrim($fqcn, '\\');
        $position = strrpos($fqcn, '\\');

        return $position === false ? $fqcn : substr($fqcn, $position + 1);
    }

    protected function isSameNamespaceSibling(string $fqcn): bool
    {
        if ($this->namespace === '') {
            return false;
        }

        $prefix = $this->namespace.'\\';

        if (! str_starts_with($fqcn, $prefix)) {
            return false;
        }

        $relative = substr($fqcn, strlen($prefix));

        return ! str_contains($relative, '\\');
    }

    protected function resolveAlias(string $fqcn): string
    {
        $parts = array_reverse(explode('\\', ltrim($fqcn, '\\')));
        $candidate = $parts[0];

        if (! isset($this->usedNames[$candidate])) {
            return $candidate;
        }

        $alias = $parts[0];

        for ($i = 1; $i < count($parts); $i++) {
            $alias .= $parts[$i];

            if (! isset($this->usedNames[$alias])) {
                return $alias;
            }
        }

        $suffix = 2;

        while (isset($this->usedNames[$alias.$suffix])) {
            $suffix++;
        }

        return $alias.$suffix;
    }

    /**
     * @return array<string, string|null>
     */
    protected function sortedImports(): array
    {
        $imports = $this->imports;
        ksort($imports);

        return $imports;
    }
}
