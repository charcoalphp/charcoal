<?php

namespace Charcoal\View;

interface LoaderInterface
{
    /**
    * @return array
    */
    public function search_path();

    /**
    * @param array $search_path
    * @throws InvalidArgumentException
    * @return MustacheLoader Chainable
    */
    public function set_search_path(array $search_path);

    /**
    * @param string $path
    * @throws InvalidArgumentException
    * @return MustacheLoader Chainable
    */
    public function add_search_path($path);

    /**
    * @param string $ident
    * @return string
    */
    public function load($ident);
}
