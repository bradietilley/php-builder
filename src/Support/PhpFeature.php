<?php

namespace BradieTilley\Builder\Support;

enum PhpFeature: string
{
    case FinalPromotedProperties = 'final_promoted_properties';

    public function since(): string
    {
        return match ($this) {
            self::FinalPromotedProperties => '8.5.0',
        };
    }
}
