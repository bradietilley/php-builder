<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Concerns\HasVisibility;
use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Contracts\ResolvesTypeImports;
use BradieTilley\Builder\Exceptions\InvalidPhpDefinitionException;
use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Builder\Support\PhpFeature;
use BradieTilley\Builder\Support\PhpTarget;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpArgument extends Data implements ExportsPhp, ResolvesTypeImports
{
    use HasVisibility;

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
        public ?string $setVisibility = null,
        public bool $readonly = false,
        public bool $final = false,
        public ?string $description = null,
        public ?PhpPropertyGetHook $get = null,
        public ?PhpPropertySetHook $set = null,
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
    ) {
        $this->type = TypeFactory::make($type);

        // Setting visibility implies constructor property promotion.
        if ($this->visibility !== null) {
            $this->promoted = true;
        }
    }

    public function isPromoted(): bool
    {
        return $this->promoted || $this->visibility !== null;
    }

    public function withResolvedImports(ImportBag $imports): static
    {
        $resolved = clone $this;
        $resolved->type = $this->type?->withResolvedImports($imports);
        $resolved->set = $this->set?->withResolvedImports($imports);
        $resolved->attributes = array_map(
            fn (PhpAttribute $attribute): PhpAttribute => $attribute->withResolvedImports($imports),
            $this->attributes,
        );

        return $resolved;
    }

    public function toPhp(int $indent = 0): string
    {
        if ($this->readonly && ($this->get !== null || $this->set !== null)) {
            throw new InvalidPhpDefinitionException('Property hooks are incompatible with readonly properties.');
        }

        if (! $this->isPromoted() && ($this->get !== null || $this->set !== null || $this->setVisibility !== null || $this->final)) {
            throw new InvalidPhpDefinitionException('Hooks, asymmetric set visibility, and final are only valid on promoted parameters.');
        }

        $parts = [];

        foreach ($this->attributes as $attribute) {
            $parts[] = $attribute->toPhp();
        }

        $signature = [];

        if ($this->isPromoted()) {
            if ($this->final && PhpTarget::supports(PhpFeature::FinalPromotedProperties)) {
                $signature[] = 'final';
            }

            array_push($signature, ...$this->visibilitySignature());

            if ($this->readonly) {
                $signature[] = 'readonly';
            }
        }

        if ($this->type !== null) {
            $signature[] = $this->type->toPhp();
        }

        $name = ($this->byRef ? '&' : '') . ($this->variadic ? '...' : '') . '$' . $this->name;
        $signature[] = $name;

        if ($this->defaultValue !== null && ! $this->variadic && $this->get === null && $this->set === null) {
            $signature[array_key_last($signature)] .= ' = ' . $this->defaultValue;
        }

        $header = implode(' ', $signature);

        if ($this->get !== null || $this->set !== null) {
            $parts[] = $header . ' {';

            if ($this->get !== null) {
                $parts[] = $this->get->toPhp(1);
            }

            if ($this->set !== null) {
                $parts[] = $this->set->toPhp(1);
            }

            $parts[] = '}';

            return $this->indentMultiline(implode("\n", $parts), $indent);
        }

        $parts[] = $header;
        $line = implode("\n", $parts);

        return $this->indentMultiline($line, $indent);
    }

    public function phpDocParamLine(): ?string
    {
        if ($this->type === null || ! $this->type->needsPhpDoc()) {
            if ($this->description === null) {
                return null;
            }

            $type = $this->type?->toPhpDoc() ?? 'mixed';

            return '@param ' . $type . ' $' . $this->name . ' ' . $this->description;
        }

        $line = '@param ' . $this->type->toPhpDoc() . ' $' . $this->name;

        if ($this->description !== null) {
            $line .= ' ' . $this->description;
        }

        return $line;
    }

    /**
     * @return list<string>
     */
    protected function visibilitySignature(): array
    {
        $visibility = $this->visibility ?? self::VISIBILITY_PUBLIC;

        if ($this->setVisibility === null || $this->setVisibility === $visibility) {
            return [$visibility];
        }

        return [$visibility, $this->setVisibility . '(set)'];
    }

    protected function indentMultiline(string $line, int $indent): string
    {
        $prefix = Indent::of($indent);

        if ($indent === 0) {
            return $line;
        }

        return $prefix . str_replace("\n", "\n" . $prefix, $line);
    }
}
