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
