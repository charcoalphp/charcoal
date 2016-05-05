<?php

namespace Charcoal\View;

interface LoaderInterface
{
    /**
     * @param string $basePath The base path to set.
     * @return LoaderInterface Chainable
     */
    public function setBasePath($basePath);

    /**
     * @return string
     */
    public function basePath();

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
     * @param string $ident The template to load.
     * @return string
     */
    public function load($ident);
}
