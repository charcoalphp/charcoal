<?php

namespace Charcoal\Cms\Support;

use InvalidArgumentException;

/**
 * Additional utilities for the HTML document.
 */
trait DocumentTrait
{
    /**
     * Parse the document title parts.
     *
     * @return string[]
     */
    protected function documentTitleParts(): array
    {
        return [
            'title' => $this->title(),
            'site'  => $this->siteName(),
        ];
    }

    /**
     * Retrieve the document title separator.
     */
    protected function documentTitleSeparator(): string
    {
        return '—';
    }

    /**
     * Parse the document title separator.
     */
    protected function parseDocumentTitleSeparator(): string
    {
        $delim = trim($this->documentTitleSeparator());
        if ($delim === '' || $delim === '0') {
            return '';
        }

        return sprintf(' %s ', $delim);
    }

    /**
     * Parse the document title.
     *
     * @param  array $parts The document title parts.
     * @return string The concatenated title.
     */
    protected function parseDocumentTitle(array $parts): string
    {
        $parts = $this->parseDocumentTitleParts($parts);
        $delim = $this->parseDocumentTitleSeparator();

        return implode($delim, $parts);
    }

    /**
     * Parse the document title segments.
     *
     * Iterates over each value in $parts passing them to
     * {@see DocumentTrait::filterDocumentTitlePart}.
     * If the method returns TRUE, the current value from $parts
     * is concatenated into the title.
     *
     * @param  array $parts The document title parts.
     * @return array The parsed and filtered segments.
     */
    protected function parseDocumentTitleParts(array $parts): array
    {
        $segments = [];
        foreach ($parts as $key => $value) {
            $value = $this->parseDocumentTitlePart($value, $key, $parts);
            if ($value === true) {
                $value = $parts[$key];
            }

            if (is_bool($value) || (empty($value) && !is_numeric($value))) {
                continue;
            }

            $segments[$key] = (string)$value;
        }

        return $segments;
    }

    /**
     * Parse the document title part.
     *
     * If you want to exclude the site name ("site") from the document title
     * if it is present in other parts, you can use the following snippet:
     *
     * ```php
     * if ($key === 'site') {
     *     foreach ($parts as $k => $v) {
     *         if ($k !== $key && strpos($v, $value) !== false) {
     *             return null;
     *         }
     *     }
     * }
     * ```
     *
     * @param  string $value The value of the current iteration.
     * @param  string $key   The key/index of the current iteration.
     * @param  array  $parts The document title parts.
     * @return mixed  The mutated value of the current iteration.
     *     If $value is equal to FALSE (converted to boolean; excluding "0"), it is excluded from the document title.
     *     If the method returns TRUE, the original $value is included into the document title.
     */
    protected function parseDocumentTitlePart($value, $key, array $parts)
    {
        unset($key, $parts);
        return $value;
    }

    /**
     * Retrieve the document title.
     *
     * @throws InvalidArgumentException If the document title structure is invalid.
     * @return string
     */
    final public function documentTitle()
    {
        $parts = $this->documentTitleParts();
        if (array_diff_key([ 'title' => true, 'site' => true ], $parts)) {
            throw new InvalidArgumentException(
                'The document title parts requires at least a "title" and a "site"'
            );
        }

        return $this->parseDocumentTitle($parts);
    }

    /**
     * Retrieve the site name.
     *
     * @return string|null
     */
    abstract public function siteName();

    /**
     * Retrieve the title of the page (from the context).
     *
     * @return string|null
     */
    abstract public function title();

    /**
     * Retrieve the current object relative to the context.
     *
     * @return \Charcoal\Model\ModelInterface
     */
    abstract public function contextObject();
}
