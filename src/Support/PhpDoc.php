<?php

namespace BradieTilley\Builder\Support;

class PhpDoc
{
    /**
     * @param  list<string>  $lines  Doc lines without leading "*"
     * @return list<string>
     */
    public static function render(array $lines, int $indent = 0): array
    {
        if ($lines === []) {
            return [];
        }

        $prefix = Indent::of($indent);
        $out = [$prefix.'/**'];

        foreach ($lines as $line) {
            $out[] = $line === '' ? $prefix.' *' : $prefix.' * '.$line;
        }

        $out[] = $prefix.' */';

        return $out;
    }
}
