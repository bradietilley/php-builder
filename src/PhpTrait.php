<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Concerns\ExportsPhpFile;
use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpTrait extends Data implements ExportsPhp
{
    use ExportsPhpFile;

    /** @var list<PhpUseTrait> */
    public array $traits = [];

    /**
     * @param  list<PhpUseTrait|string>  $traits
     * @param  list<PhpClassConstant>  $constants
     * @param  list<PhpProperty>  $properties
     * @param  list<PhpMethod>  $methods
     * @param  list<PhpAttribute>  $attributes
     */
    public function __construct(
        public string $name,
        public string $namespace = '',
        array $traits = [],
        #[ArrayOf(PhpClassConstant::class)]
        public array $constants = [],
        #[ArrayOf(PhpProperty::class)]
        public array $properties = [],
        #[ArrayOf(PhpMethod::class)]
        public array $methods = [],
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
        public bool $strictTypes = true,
    ) {
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
        return array_map(fn (PhpUseTrait $trait): string => $trait->name, $this->traits);
    }

    public function toPhp(int $indent = 0): string
    {
        $this->reserveStructuralNames();

        $traits = array_map(
            function (PhpUseTrait $trait): PhpUseTrait {
                $resolved = clone $trait;
                $resolved->name = $this->resolveTypeName($trait->name) ?? $trait->name;

                return $resolved;
            },
            $this->traits,
        );

        $body = [];

        foreach ($this->attributes as $attribute) {
            $body[] = $attribute->toPhp($indent);
        }

        $body[] = Indent::of($indent).'trait '.$this->name;
        $body[] = Indent::of($indent).'{';

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
                $constantLines[] = $constant->toPhp($indent + 1);
            }

            $sections[] = implode("\n\n", $constantLines);
        }

        if ($this->properties !== []) {
            $propertyLines = [];

            foreach ($this->properties as $property) {
                $propertyLines[] = $property->toPhp($indent + 1);
            }

            $sections[] = implode("\n\n", $propertyLines);
        }

        if ($this->methods !== []) {
            $methodLines = [];

            foreach ($this->methods as $method) {
                $methodLines[] = $method->toPhp($indent + 1);
            }

            $sections[] = implode("\n\n", $methodLines);
        }

        if ($sections !== []) {
            $body[] = implode("\n\n", $sections);
        }

        $body[] = Indent::of($indent).'}';

        if ($indent > 0) {
            return implode("\n", $body);
        }

        return $this->renderFile($body, $this->strictTypes);
    }
}
