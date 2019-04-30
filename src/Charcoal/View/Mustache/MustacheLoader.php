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
