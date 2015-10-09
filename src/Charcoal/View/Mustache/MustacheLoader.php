<?php

namespace Charcoal\View\Mustache;

use \InvalidArgumentException;

// 3rd-party libraries (`mustache/mustache`) dependencies
use \Mustache_Loader;

use \Charcoal\View\LoaderInterface;

/**
* - The mustache template loader finds a mustache template file in directories.
*/
class MustacheLoader implements
    Mustache_Loader,
    LoaderInterface
{
    /**
    * @var array $search_path
    */
    private $search_path = [];

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
    * @throws InvalidArgumentException
    * @return MustacheLoader Chainable
    */
    public function set_search_path($search_path)
    {
        if (!is_array($search_path)) {
            throw new InvalidArgumentException(
                'Search path needs to be an array'
            );
        }
        $this->search_path = [];
        foreach ($search_path as $path) {
            $this->add_search_path($path);
        }
        return $this;
    }

    /**
    * @param string $path
    * @throws InvalidArgumentException
    * @return MustacheLoader Chainable
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
            $f = $path.'/'.$filename;
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
