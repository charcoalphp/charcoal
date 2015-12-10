<?php

namespace Charcoal\View;

interface LoaderInterface
{
    /**
     * @return string[]
     */
    public function paths();

    /**
     * @param string[] $paths The list of path to add.
     * @return LoaderInterface Chainable
     */
    public function set_paths(array $paths);

    /**
     * @param string $path The path to add to the load.
     * @return LoaderInterface Chainable
     */
    public function add_path($path);

    /**
     * @param string $path The path to add (prepend) to the load.
     * @return LoaderInterface Chainable
     */
    public function prepend_path($path);

    /**
     * @param string $ident
     * @return string
     */
    public function load($ident);
}
