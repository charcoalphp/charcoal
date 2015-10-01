<?php

namespace Charcoal\View\Php;

/**
* PHP Template Loader
*/
class PhpLoader
{
    /**
    * FileLoader > search_path()
    *
    * @return array
    */
    public function search_path()
    {
        return [
            '../templates/'
        ];
    }

    /**
    * AbstractLoader > load()
    *
    * @param string $ident
    * @return string
    */
    public function load($ident)
    {
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
            $f = $path.'/'.$filename;
            if (!file_exists($f)) {
                continue;
            }
            
            ob_start();
            include $f;
            $file_content = ob_get_clean();
            ;
            
            if ($file_content !== '') {
                return $file_content;
            }
        }

        return '';
    }

    /**
    * @param string $ident
    * @return string
    */
    private function filename_from_ident($ident)
    {
        $filename = str_replace(['\\'], '.', $ident);
        $filename .= '.php';

        return $filename;
    }
}
