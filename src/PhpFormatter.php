<?php

namespace BradieTilley\Builder;

class PhpFormatter
{
    /** @var (callable(string): string)|null */
    protected static $callback = null;

    /**
     * @param  (callable(string): string)|null  $callback
     */
    public static function using(?callable $callback): void
    {
        self::$callback = $callback;
    }

    public static function clear(): void
    {
        self::$callback = null;
    }

    public static function format(string $php): string
    {
        if (self::$callback === null) {
            return $php;
        }

        return (self::$callback)($php);
    }
}
