<?php

namespace BradieTilley\Builder\Support;

use InvalidArgumentException;

/**
 * Export PHP values for generated source using short array syntax (`[]`).
 */
final class PhpValue
{
    public static function export(mixed $value, int $indent = 0): string
    {
        return self::exportValue($value, $indent, compact: false);
    }

    /**
     * Single-line export for embedding in an existing statement.
     */
    public static function exportInline(mixed $value): string
    {
        return self::exportValue($value, indent: 0, compact: true);
    }

    private static function exportValue(mixed $value, int $indent, bool $compact): string
    {
        if ($value instanceof PhpExpression) {
            return $value->code;
        }

        if (is_array($value)) {
            return self::exportArray($value, $indent, $compact);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            return var_export($value, true);
        }

        throw new InvalidArgumentException('Cannot export value of type ['.get_debug_type($value).'] as PHP.');
    }

    /**
     * @param  array<mixed>  $value
     */
    private static function exportArray(array $value, int $indent, bool $compact): string
    {
        if ($value === []) {
            return '[]';
        }

        $isList = array_is_list($value);

        if ($compact) {
            $items = [];

            foreach ($value as $key => $item) {
                $exported = self::exportValue($item, indent: 0, compact: true);
                $items[] = $isList
                    ? $exported
                    : var_export($key, true).' => '.$exported;
            }

            return '['.implode(', ', $items).']';
        }

        $inner = $indent + 1;
        $pad = Indent::of($inner);
        $close = Indent::of($indent);
        $lines = ['['];

        foreach ($value as $key => $item) {
            $exported = self::exportValue($item, $inner, compact: false);

            if ($isList) {
                $lines[] = $pad.$exported.',';
            } else {
                $lines[] = $pad.var_export($key, true).' => '.$exported.',';
            }
        }

        $lines[] = $close.']';

        return implode("\n", $lines);
    }
}
