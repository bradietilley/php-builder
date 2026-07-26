<?php

namespace BradieTilley\Builder\Concerns;

use BradieTilley\Builder\PhpTemplate;
use BradieTilley\Builder\Support\PhpDoc;

/**
 * @property string|null $description
 * @property list<PhpTemplate|string> $templates
 */
trait HasTypeDoc
{
    /**
     * @param  list<string>  $extraTags
     * @return list<string>
     */
    protected function typeDocLines(array $extraTags = []): array
    {
        $lines = [];

        if ($this->description !== null && $this->description !== '') {
            $lines[] = $this->description;
        }

        $templateTags = $this->templateTags();

        if ($templateTags !== [] || $extraTags !== []) {
            if ($lines !== []) {
                $lines[] = '';
            }

            array_push($lines, ...$templateTags, ...$extraTags);
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    protected function templateTags(): array
    {
        if ($this->templates === []) {
            return [];
        }

        $tags = [];

        foreach ($this->templates as $template) {
            if ($template instanceof PhpTemplate) {
                $tags[] = $template->toTag();

                continue;
            }

            $tags[] = '@template ' . $template;
        }

        return $tags;
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
