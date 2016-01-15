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
    public function setPaths(array $paths);

    /**
     * @param string $path The path to add to the load.
     * @return LoaderInterface Chainable
     */
    public function addPath($path);

    /**
     * @param string $path The path to add (prepend) to the load.
     * @return LoaderInterface Chainable
     */
    public function prependPath($path);

    /**
     * @param string $ident
     * @return string
     */
    public function load($ident);
}
