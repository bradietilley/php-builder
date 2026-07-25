<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpArgument extends Data implements ExportsPhp
{
    public ?PhpType $type;

    /**
     * @param  list<PhpAttribute>  $attributes
     */
    public function __construct(
        public string $name,
        PhpType|string|null $type = null,
        public ?string $defaultValue = null,
        public bool $variadic = false,
        public bool $byRef = false,
        public bool $promoted = false,
        public ?string $visibility = null,
        public bool $readonly = false,
        public ?string $description = null,
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
    ) {
        $this->type = TypeFactory::make($type);
    }

    public function toPhp(int $indent = 0): string
    {
        $parts = [];

        foreach ($this->attributes as $attribute) {
            $parts[] = $attribute->toPhp();
        }

        $signature = [];

        if ($this->promoted && $this->visibility !== null) {
            $signature[] = $this->visibility;

            if ($this->readonly) {
                $signature[] = 'readonly';
            }
        }

        if ($this->type !== null) {
            $signature[] = $this->type->toPhp();
        }

        $name = ($this->byRef ? '&' : '').($this->variadic ? '...' : '').'$'.$this->name;
        $signature[] = $name;

        if ($this->defaultValue !== null && ! $this->variadic) {
            $signature[array_key_last($signature)] .= ' = '.$this->defaultValue;
        }

        $parts[] = implode(' ', $signature);

        $line = implode("\n", $parts);

        return Indent::of($indent).str_replace("\n", "\n".Indent::of($indent), $line);
    }

    public function phpDocParamLine(): ?string
    {
        if ($this->type === null || ! $this->type->needsPhpDoc()) {
            if ($this->description === null) {
                return null;
            }

            $type = $this->type?->toPhpDoc() ?? 'mixed';

            return '@param '.$type.' $'.$this->name.' '.$this->description;
        }

        $line = '@param '.$this->type->toPhpDoc().' $'.$this->name;

        if ($this->description !== null) {
            $line .= ' '.$this->description;
        }

        return $line;
    }
}
