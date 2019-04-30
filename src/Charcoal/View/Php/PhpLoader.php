<?php

namespace Charcoal\View\Php;

// From 'charcoal-view'
use Charcoal\View\AbstractLoader;
use Charcoal\View\LoaderInterface;

/**
 * The PHP template loader finds a mustache php template file in directories and includes it (run as PHP).
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
