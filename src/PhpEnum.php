<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Concerns\ExportsPhpFile;
use BradieTilley\Builder\Concerns\HasTypeDoc;
use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpEnum extends Data implements ExportsPhp
{
    use ExportsPhpFile;
    use HasTypeDoc;

    /** @var list<string> */
    public array $implements = [];

    /**
     * @param  list<string>|string  $implements
     * @param  list<PhpEnumCase>  $cases
     * @param  list<PhpClassConstant>  $constants
     * @param  list<PhpMethod>  $methods
     * @param  list<PhpAttribute>  $attributes
     */
    public function __construct(
        public string $name,
        public string $namespace = '',
        public ?string $backedType = null,
        array|string $implements = [],
        #[ArrayOf(PhpEnumCase::class)]
        public array $cases = [],
        #[ArrayOf(PhpClassConstant::class)]
        public array $constants = [],
        #[ArrayOf(PhpMethod::class)]
        public array $methods = [],
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
        public ?string $description = null,
        public bool $strictTypes = true,
    ) {
        $this->implements = is_string($implements) ? [$implements] : $implements;
    }

    /**
     * @return list<string>
     */
    protected function structuralTypeNames(): array
    {
        return $this->implements;
    }

    public function toPhp(int $indent = 0): string
    {
        $this->reserveStructuralNames();

        $implements = array_values(array_filter(array_map(
            fn (string $interface): ?string => $this->resolveTypeName($interface),
            $this->implements,
        )));

        $body = $this->prependTypeDoc([], $indent);

        foreach ($this->attributes as $attribute) {
            $body[] = $attribute->toPhp($indent);
        }

        $signature = 'enum '.$this->name;

        if ($this->backedType !== null) {
            $signature .= ': '.$this->backedType;
        }

        if ($implements !== []) {
            $signature .= ' implements '.implode(', ', $implements);
        }

        $body[] = Indent::of($indent).$signature;
        $body[] = Indent::of($indent).'{';

        $sections = [];

        if ($this->cases !== []) {
            $caseLines = [];

            foreach ($this->cases as $case) {
                $caseLines[] = $case->toPhp($indent + 1);
            }

            $sections[] = implode("\n", $caseLines);
        }

        if ($this->constants !== []) {
            $constantLines = [];

            foreach ($this->constants as $constant) {
                $constantLines[] = $constant->toPhp($indent + 1);
            }

            $sections[] = implode("\n\n", $constantLines);
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
