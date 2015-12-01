<?php

namespace Charcoal\View\Twit;

use \InvalidArgumentExeption;

// Parent namespace dependencies
use \Charcoal\View\AbstractLoader;
use \Charcoal\View\LoaderInterface;

/**
 *
 */
class TwigLoader extends AbstractLoader implements LoaderInterface
{
    /**
     * AbstractLoader > load()
     *
     * @param string $ident The template identifier to load.
     * @throws InvalidArgumentException If the ident parameter is not a string.
     * @return string
     */
    public function load($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Template ident must be a string'
            );
        }

        // Handle dynamic template hack. @todo rename to $mustache_template
        if ($ident === '$widget_template') {
            $ident = (isset($GLOBALS['widget_template']) ? $GLOBALS['widget_template'] : null);
        }

        if ($ident === null) {
            // Error
            return '';
        }

        $filename = $this->filename_from_ident($ident);
        $search_path = $this->paths();
        foreach ($search_path as $path) {
            $f = realpath($path).'/'.$filename;
            if (!file_exists($f)) {
                continue;
            }
            
            $file_content = file_get_contents($f);
            if ($file_content !== '') {
                return $file_content;
            }
        }

        return $ident;
    }

    /**
     * @param string $ident
     * @return string
     */
    private function filename_from_ident($ident)
    {
        $filename = str_replace(['\\'], '.', $ident);
        $filename .= '.twig';

        return $filename;
    }
}
