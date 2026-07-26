<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Concerns\ExportsPhpFile;
use BradieTilley\Builder\Concerns\HasTypeDoc;
use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpInterface extends Data implements ExportsPhp
{
    use ExportsPhpFile;
    use HasTypeDoc;

    /** @var list<string> */
    public array $extends = [];

    /**
     * @param  list<string>|string  $extends
     * @param  list<PhpClassConstant>  $constants
     * @param  list<PhpProperty>  $properties
     * @param  list<PhpMethod>  $methods
     * @param  list<PhpAttribute>  $attributes
     * @param  list<string>  $docs  Extra interface docblock lines
     */
    public function __construct(
        public string $name,
        public string $namespace = '',
        array|string $extends = [],
        #[ArrayOf(PhpClassConstant::class)]
        public array $constants = [],
        #[ArrayOf(PhpProperty::class)]
        public array $properties = [],
        #[ArrayOf(PhpMethod::class)]
        public array $methods = [],
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
        public ?string $description = null,
        /** @var list<PhpTemplate|string> */
        public array $templates = [],
        #[ArrayOf('string')]
        public array $docs = [],
        public bool $strictTypes = true,
    ) {
        $this->extends = is_string($extends) ? [$extends] : $extends;
    }

    /**
     * @return list<string>
     */
    protected function structuralTypeNames(): array
    {
        return $this->extends;
    }

    public function toPhp(int $indent = 0): string
    {
        $this->reserveStructuralNames();

        $extends = array_values(array_filter(array_map(
            fn (string $interface): ?string => $this->resolveTypeName($interface),
            $this->extends,
        )));

        $imports = $this->imports();
        $body = $this->prependTypeDoc([], $indent);

        foreach ($this->attributes as $attribute) {
            $body[] = $attribute->withResolvedImports($imports)->toPhp($indent);
        }

        $signature = ['interface', $this->name];

        if ($extends !== []) {
            $signature[] = 'extends';
            $signature[] = implode(', ', $extends);
        }

        $body[] = Indent::of($indent) . implode(' ', $signature);
        $body[] = Indent::of($indent) . '{';

        $sections = [];

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
                $method = $method->withResolvedImports($imports);
                $method->signatureOnly = true;
                $method->abstract = false;
                $methodLines[] = $method->toPhp($indent + 1);
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
}
