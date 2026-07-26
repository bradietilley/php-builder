<?php

namespace BradieTilley\Builder\Support;

use BradieTilley\Builder\Exceptions\InvalidPhpDefinitionException;
use BradieTilley\Builder\PhpArgument;
use Closure;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure as ClosureNode;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use ReflectionFunction;
use ReflectionParameter;

class ClosureSource
{
    /**
     * @return array{
     *     args: list<PhpArgument>,
     *     return: \BradieTilley\Builder\Contracts\PhpType|null,
     *     lines: list<string>,
     *     static: bool,
     *     returnsReference: bool,
     * }
     */
    public static function extract(Closure $closure): array
    {
        $reflection = new ReflectionFunction($closure);
        $node = self::locateClosureNode($reflection);

        if ($node instanceof ClosureNode && $node->uses !== []) {
            $names = array_map(
                function (Node\Expr\ClosureUse $use): string {
                    $name = $use->var->name;

                    return is_string($name) ? '$' . $name : '$…';
                },
                $node->uses,
            );

            throw new InvalidPhpDefinitionException(
                'Closures with use() bindings cannot be inlined into generated methods (found: ' . implode(', ', $names) . ').',
            );
        }

        return [
            'args' => self::arguments($reflection, $node),
            'return' => ReflectionTypeFactory::make($reflection->getReturnType()),
            'lines' => self::bodyLines($node),
            'static' => $reflection->isStatic(),
            'returnsReference' => $reflection->returnsReference(),
        ];
    }

    private static function locateClosureNode(ReflectionFunction $reflection): ClosureNode|ArrowFunction
    {
        $file = $reflection->getFileName();

        if ($file === false || ! is_file($file)) {
            throw new InvalidPhpDefinitionException('Cannot inline a closure that has no source file.');
        }

        $start = $reflection->getStartLine();
        $end = $reflection->getEndLine();

        if ($start === false || $end === false) {
            throw new InvalidPhpDefinitionException('Cannot determine source lines for closure.');
        }

        $code = file_get_contents($file);

        if ($code === false) {
            throw new InvalidPhpDefinitionException("Unable to read closure source file [{$file}].");
        }

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($code);

        if ($ast === null) {
            throw new InvalidPhpDefinitionException("Unable to parse closure source file [{$file}].");
        }

        $finder = new NodeFinder();
        /** @var list<ClosureNode|ArrowFunction> $candidates */
        $candidates = $finder->find($ast, function (Node $node) use ($start, $end): bool {
            if (! $node instanceof ClosureNode && ! $node instanceof ArrowFunction) {
                return false;
            }

            $nodeStart = $node->getStartLine();
            $nodeEnd = $node->getEndLine();

            return $nodeStart >= $start && $nodeEnd <= $end;
        });

        if ($candidates === []) {
            throw new InvalidPhpDefinitionException(
                "Unable to locate closure AST between lines {$start}-{$end} in [{$file}].",
            );
        }

        // Prefer the outermost closure. Nested arrow functions / closures share the
        // reflection line range but must not be selected for body extraction.
        usort(
            $candidates,
            static function (ClosureNode|ArrowFunction $a, ClosureNode|ArrowFunction $b): int {
                return [$a->getStartLine(), -$a->getEndLine()]
                    <=> [$b->getStartLine(), -$b->getEndLine()];
            },
        );

        return $candidates[0];
    }

    /**
     * @return list<PhpArgument>
     */
    private static function arguments(
        ReflectionFunction $reflection,
        ClosureNode|ArrowFunction $node,
    ): array {
        $defaults = self::defaultExpressions($node);
        $args = [];

        foreach ($reflection->getParameters() as $index => $parameter) {
            $args[] = new PhpArgument(
                name: $parameter->getName(),
                type: ReflectionTypeFactory::make($parameter->getType()),
                defaultValue: $defaults[$index] ?? self::fallbackDefault($parameter),
                variadic: $parameter->isVariadic(),
                byRef: $parameter->isPassedByReference(),
            );
        }

        return $args;
    }

    /**
     * @return array<int, string>
     */
    private static function defaultExpressions(ClosureNode|ArrowFunction $node): array
    {
        $printer = new Standard();
        /** @var array<int, string> $defaults */
        $defaults = [];

        foreach (array_values($node->params) as $index => $param) {
            if ($param->default === null) {
                continue;
            }

            $defaults[$index] = $printer->prettyPrintExpr($param->default);
        }

        return $defaults;
    }

    private static function fallbackDefault(ReflectionParameter $parameter): ?string
    {
        if (! $parameter->isDefaultValueAvailable()) {
            return null;
        }

        if ($parameter->isDefaultValueConstant()) {
            $name = $parameter->getDefaultValueConstantName();

            return $name !== null ? $name : null;
        }

        return var_export($parameter->getDefaultValue(), true);
    }

    /**
     * @return list<string>
     */
    private static function bodyLines(ClosureNode|ArrowFunction $node): array
    {
        $printer = new Standard();

        if ($node instanceof ArrowFunction) {
            $expr = $printer->prettyPrintExpr($node->expr);

            return $node->byRef
                ? ['return &' . $expr . ';']
                : ['return ' . $expr . ';'];
        }

        if ($node->stmts === []) {
            return [];
        }

        $code = $printer->prettyPrint($node->stmts);
        $lines = preg_split("/\r\n|\n|\r/", $code);

        return $lines === false ? [] : $lines;
    }
}
