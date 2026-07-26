<?php

namespace BradieTilley\Builder\Support;

class PhpTarget
{
    public const DEFAULT = '8.3.0';

    protected static ?string $version = null;

    public static function using(?string $version): void
    {
        if ($version === null) {
            self::$version = null;

            return;
        }

        self::$version = self::normalize($version);
    }

    public static function clear(): void
    {
        self::$version = null;
    }

    public static function current(): string
    {
        return self::normalize(PHP_VERSION);
    }

    public static function version(): string
    {
        return self::$version ?? self::DEFAULT;
    }

    public static function supports(PhpFeature $feature): bool
    {
        return version_compare(self::version(), $feature->since(), '>=');
    }

    protected static function normalize(string $version): string
    {
        $version = ltrim(trim($version), 'vV');

        if (preg_match('/^\d+\.\d+$/', $version) === 1) {
            return $version . '.0';
        }

        if (preg_match('/^\d+\.\d+\.\d+/', $version, $matches) === 1) {
            return $matches[0];
        }

        return $version;
    }
}
