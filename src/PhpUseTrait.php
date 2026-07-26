<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Data;

class PhpUseTrait extends Data implements ExportsPhp
{
    /** @var list<string> */
    public array $names = [];

    /** @var list<PhpTraitAlias> */
    public array $aliases = [];

    /** @var list<PhpTraitInsteadof> */
    public array $insteadof = [];

    /**
     * @param  string|list<string>  $name  One or more trait names for a shared use block
     * @param  array<string, string>|list<PhpTraitAlias>  $aliases
     * @param  array<string, string>|list<PhpTraitInsteadof>  $insteadof
     */
    public function __construct(
        string|array $name,
        array $aliases = [],
        array $insteadof = [],
    ) {
        $this->names = is_string($name) ? [$name] : $name;
        $this->aliases = $this->normalizeAliases($aliases);
        $this->insteadof = $this->normalizeInsteadof($insteadof, $this->names[0] ?? '');
    }

    /**
     * @return list<string>
     */
    public function allNames(): array
    {
        return $this->names;
    }

    public function toPhp(int $indent = 0): string
    {
        $prefix = Indent::of($indent);
        $list = implode(', ', $this->names);

        if ($this->aliases === [] && $this->insteadof === []) {
            return $prefix . 'use ' . $list . ';';
        }

        $lines = [$prefix . 'use ' . $list . ' {'];

        foreach ($this->insteadof as $adaptation) {
            $lines[] = $adaptation->toPhp($indent + 1);
        }

        foreach ($this->aliases as $alias) {
            $lines[] = $alias->toPhp($indent + 1);
        }

        $lines[] = $prefix . '}';

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, string>|list<PhpTraitAlias>  $aliases
     * @return list<PhpTraitAlias>
     */
    protected function normalizeAliases(array $aliases): array
    {
        $normalized = [];

        foreach ($aliases as $method => $alias) {
            if ($alias instanceof PhpTraitAlias) {
                $normalized[] = $alias;

                continue;
            }

            $normalized[] = new PhpTraitAlias(method: (string) $method, alias: $alias);
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>|list<PhpTraitInsteadof>  $insteadof
     * @return list<PhpTraitInsteadof>
     */
    protected function normalizeInsteadof(array $insteadof, string $defaultFrom): array
    {
        $normalized = [];

        foreach ($insteadof as $method => $otherTrait) {
            if ($otherTrait instanceof PhpTraitInsteadof) {
                $normalized[] = $otherTrait;

                continue;
            }

            $normalized[] = new PhpTraitInsteadof(
                method: (string) $method,
                from: $defaultFrom,
                insteadOf: $otherTrait,
            );
        }

        return $normalized;
    }
}
