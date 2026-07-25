<?php

namespace BradieTilley\Builder\Concerns;

use BradieTilley\Builder\Support\PhpDoc;

trait HasTypeDoc
{
    /**
     * @param  list<string>  $extraTags
     * @return list<string>
     */
    protected function typeDocLines(array $extraTags = []): array
    {
        $lines = [];

        if (($this->description ?? null) !== null && $this->description !== '') {
            $lines[] = $this->description;
        }

        if ($extraTags !== []) {
            if ($lines !== []) {
                $lines[] = '';
            }

            array_push($lines, ...$extraTags);
        }

        return $lines;
    }

    /**
     * @param  list<string>  $body
     * @param  list<string>  $extraTags
     * @return list<string>
     */
    protected function prependTypeDoc(array $body, int $indent, array $extraTags = []): array
    {
        $doc = PhpDoc::render($this->typeDocLines($extraTags), $indent);

        if ($doc === []) {
            return $body;
        }

        return [...$doc, ...$body];
    }
}
