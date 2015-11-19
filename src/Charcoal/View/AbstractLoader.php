<?php

namespace Charcoal\View;

// PHP Dependencies
use \InvalidArgumentException;

/**
*
*/
abstract class AbstractLoader
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
            // Use default templates path if none was set
            return [
                '../templates/'
            ];
        }
        return $this->search_path;
    }

    /**
    * @param array $search_path
    * @return MustacheLoader Chainable
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
    * @param string $ident
    * @return string
    */
    abstract public function load($ident);
}
