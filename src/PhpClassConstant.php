<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Concerns\HasVisibility;
use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Contracts\ResolvesTypeImports;
use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpClassConstant extends Data implements ExportsPhp, ResolvesTypeImports
{
    use HasVisibility;

    public ?PhpType $type;

    /**
     * @param  list<PhpAttribute>  $attributes
     */
    public function __construct(
        public string $name,
        public string $value,
        public PhpVisibility $visibility = PhpVisibility::Public,
        public bool $final = false,
        PhpType|string|null $type = null,
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
    ) {
        $this->type = TypeFactory::make($type);
    }

    public function withResolvedImports(ImportBag $imports): static
    {
        $resolved = clone $this;
        $resolved->type = $this->type?->withResolvedImports($imports);
        $resolved->attributes = array_map(
            fn (PhpAttribute $attribute): PhpAttribute => $attribute->withResolvedImports($imports),
            $this->attributes,
        );

        return $resolved;
    }

    public function toPhp(int $indent = 0): string
    {
        $prefix = Indent::of($indent);
        $lines = [];

        foreach ($this->attributes as $attribute) {
            $lines[] = $attribute->toPhp($indent);
        }

        $parts = [];

        if ($this->final) {
            $parts[] = 'final';
        }

        $parts[] = $this->visibility->value;
        $parts[] = 'const';

        if ($this->type !== null) {
            $parts[] = $this->type->toPhp();
        }

        $parts[] = $this->name . ' = ' . $this->value . ';';

        $lines[] = $prefix . implode(' ', $parts);

        return implode("\n", $lines);
    }
}
