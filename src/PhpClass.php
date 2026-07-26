<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Concerns\ExportsPhpFile;
use BradieTilley\Builder\Concerns\HasTypeDoc;
use BradieTilley\Builder\Concerns\HasVisibility;
use BradieTilley\Builder\Concerns\ResolvesTraitUses;
use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpClass extends Data implements ExportsPhp
{
    use ExportsPhpFile;
    use HasTypeDoc;
    use HasVisibility;
    use ResolvesTraitUses;

    /** @var list<string> */
    public array $implements = [];

    /** @var list<PhpUseTrait> */
    public array $traits = [];

    /**
     * @param  list<string>|string  $implements
     * @param  list<PhpUseTrait|string>  $traits
     * @param  list<PhpClassConstant>  $constants
     * @param  list<PhpProperty>  $properties
     * @param  list<PhpMethod>  $methods
     * @param  list<PhpAttribute>  $attributes
     * @param  list<PhpTemplate|string>  $templates
     */
    public function __construct(
        public string $name,
        public string $namespace = '',
        public ?string $extends = null,
        array|string $implements = [],
        array $traits = [],
        #[ArrayOf(PhpClassConstant::class)]
        public array $constants = [],
        #[ArrayOf(PhpProperty::class)]
        public array $properties = [],
        #[ArrayOf(PhpMethod::class)]
        public array $methods = [],
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
        public ?string $description = null,
        public array $templates = [],
        public bool $abstract = false,
        public bool $final = false,
        public bool $readonly = false,
        public bool $strictTypes = true,
    ) {
        $this->implements = is_string($implements) ? [$implements] : $implements;
        $this->traits = array_map(
            fn (PhpUseTrait|string $trait): PhpUseTrait => is_string($trait) ? new PhpUseTrait($trait) : $trait,
            $traits,
        );
    }

    /**
     * @return list<string>
     */
    protected function structuralTypeNames(): array
    {
        $names = [];

        if ($this->extends !== null) {
            $names[] = $this->extends;
        }

        foreach ($this->implements as $interface) {
            $names[] = $interface;
        }

        foreach ($this->traits as $trait) {
            array_push($names, ...$trait->allNames());
        }

        return $names;
    }

    public function toPhp(int $indent = 0): string
    {
        $this->reserveStructuralNames();

        $extends = $this->resolveTypeName($this->extends);
        $implements = array_values(array_filter(array_map(
            fn (string $interface): ?string => $this->resolveTypeName($interface),
            $this->implements,
        )));
        $traits = array_map(
            fn (PhpUseTrait $trait): PhpUseTrait => $this->resolveTraitUse($trait),
            $this->traits,
        );

        $imports = $this->imports();
        $body = $this->prependTypeDoc([], $indent);

        foreach ($this->attributes as $attribute) {
            $body[] = $attribute->withResolvedImports($imports)->toPhp($indent);
        }

        $body[] = Indent::of($indent) . $this->classSignature($extends, $implements);
        $body[] = Indent::of($indent) . '{';

        $sections = [];

        if ($traits !== []) {
            $traitLines = [];

            foreach ($traits as $trait) {
                $traitLines[] = $trait->toPhp($indent + 1);
            }

            $sections[] = implode("\n", $traitLines);
        }

        if ($this->constants !== []) {
            $constantLines = [];

            foreach ($this->constants as $constant) {
                $constantLines[] = $constant->withResolvedImports($imports)->toPhp($indent + 1);
            }

            $sections[] = implode("\n\n", $constantLines);
        }

        if ($this->properties !== []) {
            $propertyLines = [];

            foreach ($this->properties as $property) {
                $propertyLines[] = $property->withResolvedImports($imports)->toPhp($indent + 1);
            }

            $sections[] = implode("\n\n", $propertyLines);
        }

        if ($this->methods !== []) {
            $methodLines = [];

            foreach ($this->methods as $method) {
                $methodLines[] = $method->withResolvedImports($imports)->toPhp($indent + 1);
            }

            $sections[] = implode("\n\n", $methodLines);
        }

        if ($sections !== []) {
            $body[] = implode("\n\n", $sections);
        }

        $body[] = Indent::of($indent) . '}';

        if ($indent > 0) {
            return implode("\n", $body);
        }

        return $this->renderFile($body, $this->strictTypes);
    }

    /**
     * @param  list<string>  $implements
     */
    protected function classSignature(?string $extends, array $implements): string
    {
        $parts = [];

        if ($this->abstract) {
            $parts[] = 'abstract';
        } elseif ($this->final) {
            $parts[] = 'final';
        }

        if ($this->readonly) {
            $parts[] = 'readonly';
        }

        $parts[] = 'class';
        $parts[] = $this->name;

        if ($extends !== null) {
            $parts[] = 'extends';
            $parts[] = $extends;
        }

        if ($implements !== []) {
            $parts[] = 'implements';
            $parts[] = implode(', ', $implements);
        }

        return implode(' ', $parts);
    }
}
