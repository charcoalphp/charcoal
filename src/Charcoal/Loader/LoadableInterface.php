<?php

namespace Charcoal\Loader;

/**
* Loadable Interface defined object that can be loaded through a Loader
*/
interface LoadableInterface
{
    /**
    * Set the loader object.
    *
    * @param LoaderInterface $loader
    * @return LoadableInterface Chainable
    */
    public function set_loader(LoaderInterface $loader);

    /**
    * Get the loader object.
    *
    * @return LoaderInterface
    */
    public function loader();

    /**
    *
    */
    public function load();
}
