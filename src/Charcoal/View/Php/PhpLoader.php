<?php

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
     * @return boolean
     */
    protected function isTemplateString($ident)
    {
        return strpos($ident, '<?') !== false || parent::isTemplateString($ident);
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param string $ident The identifier to convert.
     * @return string
     */
    protected function filenameFromIdent($ident)
    {
        $filename = str_replace([ '\\' ], '.', $ident);
        $filename .= '.php';

        return $filename;
    }
}
