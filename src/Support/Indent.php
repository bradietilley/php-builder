<?php

namespace BradieTilley\Builder\Support;

class Indent
{
    public const SPACES = 4;

    public static function of(int $level): string
    {
        return str_repeat(' ', max(0, $level) * self::SPACES);
    }

    /**
     * @param  list<string>  $lines
     * @return list<string>
     */
    public static function lines(array $lines, int $level): array
    {
        $prefix = self::of($level);

        return array_map(
            fn (string $line): string => $line === '' ? '' : $prefix.$line,
            $lines,
        );
    }

    /**
     * @param  list<string>  $lines
     */
    public static function join(array $lines, int $level = 0): string
    {
        return implode("\n", self::lines($lines, $level));
    }
}
