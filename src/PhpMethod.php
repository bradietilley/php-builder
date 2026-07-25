<?php

namespace BradieTilley\Builder;

use BradieTilley\Builder\Concerns\HasVisibility;
use BradieTilley\Builder\Contracts\ExportsPhp;
use BradieTilley\Builder\Contracts\PhpType;
use BradieTilley\Builder\Support\Indent;
use BradieTilley\Builder\Support\PhpDoc;
use BradieTilley\Builder\Support\TypeFactory;
use BradieTilley\Data\Attributes\ArrayOf;
use BradieTilley\Data\Data;

class PhpMethod extends Data implements ExportsPhp
{
    use HasVisibility;

    public ?PhpType $return;

    /**
     * @param  list<PhpArgument>  $args
     * @param  list<string>  $lines
     * @param  list<string>  $throws  Exception type names for @throws tags
     * @param  list<PhpAttribute>  $attributes
     */
    public function __construct(
        public string $name,
        public string $visibility = self::VISIBILITY_PUBLIC,
        public bool $static = false,
        public bool $final = false,
        public bool $abstract = false,
        public bool $returnsReference = false,
        #[ArrayOf(PhpArgument::class)]
        public array $args = [],
        PhpType|string|null $return = null,
        #[ArrayOf('string')]
        public array $lines = [],
        public ?string $description = null,
        #[ArrayOf('string')]
        public array $throws = [],
        /** @var list<PhpTemplate|string> */
        public array $templates = [],
        #[ArrayOf(PhpAttribute::class)]
        public array $attributes = [],
        public bool $signatureOnly = false,
    ) {
        $this->return = TypeFactory::make($return);
    }

    public function toPhp(int $indent = 0): string
    {
        $prefix = Indent::of($indent);
        $out = [];

        foreach (PhpDoc::render($this->phpDocLines(), $indent) as $docLine) {
            $out[] = $docLine;
        }

        foreach ($this->attributes as $attribute) {
            $out[] = $attribute->toPhp($indent);
        }

        $signature = [];

        if ($this->final && ! $this->abstract) {
            $signature[] = 'final';
        }

        if ($this->abstract) {
            $signature[] = 'abstract';
        }

        $signature[] = $this->visibility;

        if ($this->static) {
            $signature[] = 'static';
        }

        $signature[] = 'function';
        $argsPhp = $this->argumentsSignature($indent);
        $functionName = ($this->returnsReference ? '&' : '').$this->name;
        $signature[] = $functionName.$argsPhp['open'];

        $header = implode(' ', $signature);

        if ($argsPhp['multiline']) {
            $out[] = $prefix.$header;

            foreach ($argsPhp['lines'] as $argLine) {
                $out[] = $argLine;
            }

            $close = $prefix.')';

            if ($this->return !== null) {
                $close .= ': '.$this->return->toPhp();
            }

            if ($this->abstract || $this->signatureOnly) {
                $out[] = $close.';';

                return implode("\n", $out);
            }

            $out[] = $close.' {';
        } else {
            if ($this->return !== null) {
                $header .= ': '.$this->return->toPhp();
            }

            if ($this->abstract || $this->signatureOnly) {
                $out[] = $prefix.$header.';';

                return implode("\n", $out);
            }

            $out[] = $prefix.$header;
            $out[] = $prefix.'{';
        }

        foreach ($this->lines as $line) {
            $out[] = $line === '' ? '' : Indent::of($indent + 1).$line;
        }

        $out[] = $prefix.'}';

        return implode("\n", $out);
    }

    /**
     * @return array{open: string, multiline: bool, lines: list<string>}
     */
    protected function argumentsSignature(int $indent): array
    {
        if ($this->args === []) {
            return ['open' => '()', 'multiline' => false, 'lines' => []];
        }

        $rendered = array_map(
            fn (PhpArgument $arg): string => trim($arg->toPhp()),
            $this->args,
        );

        $multiline = count($rendered) > 1;

        foreach ($rendered as $arg) {
            if (str_contains($arg, "\n")) {
                $multiline = true;

                break;
            }
        }

        if (! $multiline) {
            return [
                'open' => '('.implode(', ', $rendered).')',
                'multiline' => false,
                'lines' => [],
            ];
        }

        $lines = [];
        $argIndent = Indent::of($indent + 1);

        foreach ($rendered as $index => $arg) {
            $suffix = $index < count($rendered) - 1 ? ',' : ',';
            $argLines = explode("\n", $arg);
            $argLines[array_key_last($argLines)] .= $suffix;

            foreach ($argLines as $i => $argLine) {
                $lines[] = ($i === 0 ? $argIndent : $argIndent).$argLine;
            }
        }

        return [
            'open' => '(',
            'multiline' => true,
            'lines' => $lines,
        ];
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

        $tags = [];

        foreach ($this->templates as $template) {
            $tags[] = $template instanceof PhpTemplate
                ? $template->toTag()
                : '@template '.$template;
        }

        foreach ($this->args as $arg) {
            $param = $arg->phpDocParamLine();

            if ($param !== null) {
                $tags[] = $param;
            }
        }

        if ($this->return !== null && $this->return->needsPhpDoc()) {
            $tags[] = '@return '.$this->return->toPhpDoc();
        }

        foreach ($this->throws as $throw) {
            $tags[] = '@throws '.$throw;
        }

        if ($tags !== []) {
            if ($lines !== []) {
                $lines[] = '';
            }

            array_push($lines, ...$tags);
        }

        return $lines;
    }
}
