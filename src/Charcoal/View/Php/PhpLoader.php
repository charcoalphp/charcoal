<?php

namespace Charcoal\View\Php;

use \Charcoal\View\LoaderInterface;

/**
* The PHP template loader finds a mustache php template file in directories and includes it (run as PHP).
*/
class PhpLoader implements LoaderInterface
{
    /**
    * FileLoader > search_path()
    *
    * @return array
    */
    public function search_path()
    {
        if (empty($this->search_path)) {
            return [
                '../templates/'
            ];
        }
        return $this->search_path;
    }

        /**
    * @param array $search_path
    * @return PhpLoader Chainable
    */
    public function set_search_path(array $search_path)
    {
        $this->search_path = [];
        foreach ($search_path as $path) {
            $this->add_search_path($path);
        }
        return $this;
    }

    /**
    * @param string $path
    * @throws InvalidArgumentException
    * @return PhpLoader Chainable
    */
    public function add_search_path($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path needs to be a string'
            );
        }
        $this->search_path[] = $path;
        return $this;
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
            $f = realpath($path).'/'.$filename;
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

        return $ident;
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
