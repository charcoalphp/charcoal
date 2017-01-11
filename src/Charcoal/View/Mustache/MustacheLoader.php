<?php

namespace Charcoal\View\Mustache;

// PHP Dependencies
use \InvalidArgumentException;

// 3rd-party libraries (`mustache/mustache`) dependencies
use \Mustache_Loader;

// Parent namespace dependencies
use \Charcoal\View\AbstractLoader;
use \Charcoal\View\LoaderInterface;

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
