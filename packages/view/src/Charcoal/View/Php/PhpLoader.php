<?php

declare(strict_types=1);

namespace Charcoal\View\Php;

// From 'charcoal-view'
use Charcoal\View\AbstractLoader;
use Charcoal\View\LoaderInterface;

/**
 * PHP Template Loader
 *
 * Finds a PHP template file in a collection of directory paths.
 */
class PhpLoader extends AbstractLoader implements LoaderInterface
{
    /**
     * Determine if the variable is a template literal.
     *
     * This method looks for any PHP tags in the given string,
     * which a file path would most likely not have.
     *
     * @todo   Add support for custom delimiters.
     * @param  string $ident The template being evaluated.
     */
    #[\Override]
    protected function isTemplateString(string $ident): bool
    {
        return str_contains($ident, '<?') || parent::isTemplateString($ident);
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param string $ident The identifier to convert.
     */
    protected function filenameFromIdent(string $ident): string
    {
        $filename = str_replace([ '\\' ], '.', $ident);

        return $filename . '.php';
    }
}
