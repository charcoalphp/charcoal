<?php

namespace Charcoal\View\Mustache;

// From Mustache
use Mustache_Loader as MustacheLoaderInterface;

// From 'charcoal-view'
use Charcoal\View\AbstractLoader;
use Charcoal\View\LoaderInterface;

/**
 * Mustache Template Loader
 *
 * Finds a Mustache template file in a collection of directory paths.
 */
class MustacheLoader extends AbstractLoader implements
    LoaderInterface,
    MustacheLoaderInterface
{
    /**
     * Determine if the variable is a template literal.
     *
     * This method looks for any tag delimiters in the given string,
     * which a file path would most likely not have.
     *
     * @todo   Add support for custom delimiters.
     * @param  string $ident The template being evaluated.
     * @return boolean
     */
    protected function isTemplateString($ident)
    {
        return strpos($ident, '{{') !== false || parent::isTemplateString($ident);
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param  string $ident The template identifier to convert to a filename.
     * @return string
     */
    protected function filenameFromIdent($ident)
    {
        $filename  = str_replace([ '\\' ], '.', $ident);
        $filename .= '.mustache';

        return $filename;
    }
}
