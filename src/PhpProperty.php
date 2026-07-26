<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Concerns\HasVisibility;
use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Contracts\ResolvesTypeImports;
use BradieTilley\Builder\Exceptions\InvalidPhpDefinitionException;
use BradieTilley\Builder\Support\ImportBag;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Builder\Support\PhpDoc;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpProperty extends Data implements ExportsPhp, ResolvesTypeImports
{
    use HasVisibility;

    public ?PhpType $type;

    /**
     * @param  list<PhpAttribute>  $attributes
     */
    public function __construct(
        public string $name,
        PhpType|string|null $type = null,
        public PhpVisibility $visibility = PhpVisibility::Public,
        public ?PhpVisibility $setVisibility = null,
        public bool $static = false,
        public bool $readonly = false,
        public bool $abstract = false,
        public bool $final = false,
        public ?string $defaultValue = null,
        public ?string $description = null,
        public ?PhpPropertyGetHook $get = null,
        public ?PhpPropertySetHook $set = null,
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
    ) {
        $this->type = TypeFactory::make($type);
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

        if ($this->abstract && $this->final) {
            throw new InvalidPhpDefinitionException('Properties cannot be both abstract and final.');
        }

        if ($this->abstract && $this->get === null && $this->set === null) {
            throw new InvalidPhpDefinitionException('Abstract properties must declare at least one hook.');
        }

        if ($this->abstract && $this->defaultValue !== null) {
            throw new InvalidPhpDefinitionException('Abstract properties cannot have a default value.');
        }

        $prefix = Indent::of($indent);
        $lines = [];

        foreach (PhpDoc::render($this->phpDocLines(), $indent) as $docLine) {
            $lines[] = $docLine;
        }

        foreach ($this->attributes as $attribute) {
            $lines[] = $attribute->toPhp($indent);
        }

        $signature = [];

        if ($this->abstract) {
            $signature[] = 'abstract';
        }

        if ($this->final) {
            $signature[] = 'final';
        }

        array_push($signature, ...$this->visibilitySignature());

        if ($this->static) {
            $signature[] = 'static';
        }

        if ($this->readonly) {
            $signature[] = 'readonly';
        }

        if ($this->type !== null) {
            $signature[] = $this->type->toPhp();
        }

        $signature[] = '$' . $this->name;

        $header = implode(' ', $signature);

        if ($this->defaultValue !== null && $this->get === null && $this->set === null) {
            $header .= ' = ' . $this->defaultValue;
        }

        if ($this->get === null && $this->set === null) {
            $lines[] = $prefix . $header . ';';

            return implode("\n", $lines);
        }

        $lines[] = $prefix . $header . ' {';

        if ($this->get !== null) {
            $lines[] = $this->get->toPhp($indent + 1);
        }

        if ($this->set !== null) {
            $lines[] = $this->set->toPhp($indent + 1);
        }

        $lines[] = $prefix . '}';

        return implode("\n", $lines);
    }

    /**
     * @return list<string>
     */
    protected function phpDocLines(): array
    {
        $lines = [];

        if ($this->description !== null) {
            $lines[] = $this->description;
        }

        if ($this->type !== null && $this->type->needsPhpDoc()) {
            if ($lines !== []) {
                $lines[] = '';
            }

            $lines[] = '@var ' . $this->type->toPhpDoc() . ' $' . $this->name;
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    protected function visibilitySignature(): array
    {
        if ($this->setVisibility === null || $this->setVisibility === $this->visibility) {
            return [$this->visibility->value];
        }

        return [$this->visibility->value, $this->setVisibility->value . '(set)'];
    }
}
