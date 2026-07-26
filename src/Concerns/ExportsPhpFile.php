<?php

namespace BradieTilley\Builder\Concerns;

use BradieTilley\Builder\PhpFormatter;
use BradieTilley\Builder\Support\ImportBag;

/**
 * @property string $namespace
 */
trait ExportsPhpFile
{
    protected ?ImportBag $importBag = null;

    public function imports(): ImportBag
    {
        return $this->importBag ??= new ImportBag($this->namespace);
    }

    public function import(string $fqcn, ?string $alias = null): string
    {
        $this->reserveStructuralNames();

        return $this->imports()->import($fqcn, $alias);
    }

    /**
     * Reserve extends/implements/traits names before user imports so clashes resolve correctly.
     */
    protected function reserveStructuralNames(): void
    {
        foreach ($this->structuralTypeNames() as $name) {
            $this->imports()->reserve($name);
        }
    }

    /**
     * @return list<string>
     */
    protected function structuralTypeNames(): array
    {
        return [];
    }

    /**
     * @param  list<string>  $bodyLines
     */
    protected function renderFile(array $bodyLines, bool $strict = true): string
    {
        $lines = [
            '<?php',
            '',
        ];

        if ($strict) {
            $lines[] = 'declare(strict_types=1);';
            $lines[] = '';
        }

        if ($this->namespace !== '') {
            $lines[] = 'namespace ' . $this->namespace . ';';
            $lines[] = '';
        }

        $useLines = $this->imports()->toUseLines();

        if ($useLines !== []) {
            array_push($lines, ...$useLines);
            $lines[] = '';
        }

        array_push($lines, ...$bodyLines);

        if ($bodyLines === [] || end($bodyLines) !== '') {
            $lines[] = '';
        }

        return PhpFormatter::format(implode("\n", $lines));
    }

    protected function resolveTypeName(?string $fqcn): ?string
    {
        if ($fqcn === null || $fqcn === '') {
            return null;
        }

        return $this->imports()->import($fqcn);
    }
}
