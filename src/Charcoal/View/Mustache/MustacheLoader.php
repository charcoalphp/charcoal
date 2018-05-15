<?php

namespace Charcoal\View\Mustache;

// From Mustache
use Mustache_Loader;

// From 'charcoal-view'
use Charcoal\View\AbstractLoader;
use Charcoal\View\LoaderInterface;

/**
 * - The mustache template loader finds a mustache template file in directories.
 */
class MustacheLoader extends AbstractLoader implements
    Mustache_Loader,
    LoaderInterface
{
    /**
     * Convert an identifier to a file path.
     *
     * @param string $ident The template identifier to convert to a filename.
     * @return string
     */
    protected function filenameFromIdent($ident)
    {
        $filename  = str_replace([ '\\' ], '.', $ident);
        $filename .= '.mustache';

        return $filename;
    }
}
