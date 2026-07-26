<?php

namespace BradieTilley\Builder\Concerns;

use BradieTilley\Builder\PhpVisibility;

trait HasVisibility
{
    public static function public(): PhpVisibility
    {
        return PhpVisibility::Public;
    }

    public static function protected(): PhpVisibility
    {
        return PhpVisibility::Protected;
    }

    public static function private(): PhpVisibility
    {
        return PhpVisibility::Private;
    }
}
