<?php

namespace BradieTilley\Builder;

use BradieTilley\Data\Data;

class PhpTemplate extends Data
{
    public function __construct(
        public string $name,
        public ?string $of = null,
        public bool $covariant = false,
        public bool $contravariant = false,
    ) {
    }

    public function toTag(): string
    {
        $tag = '@template';

        if ($this->covariant) {
            $tag .= ' covariant';
        } elseif ($this->contravariant) {
            $tag .= ' contravariant';
        }

        $tag .= ' ' . $this->name;

        if ($this->of !== null) {
            $tag .= ' of ' . $this->of;
        }

        return $tag;
    }
}
