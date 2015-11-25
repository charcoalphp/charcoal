<?php

namespace Charcoal\View\Mustache;

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
    * AbstractLoader > load()
    *
    * @param string $ident
    * @return string
    */
    public function load($ident)
    {
        if(!is_string($ident)) {
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
        $search_path = $this->search_path();
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
        $filename .= '.mustache';

        return $filename;
    }
}
