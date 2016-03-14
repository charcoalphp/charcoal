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
    * @param array|LoaderInterface $loader The object loader.
    * @return LoadableInterface Chainable
    */
    public function setLoader($loader);

    /**
    * Get the loader object.
    *
    * @return LoaderInterface
    */
    public function loader();

    /**
    * @return mixed
    */
    public function load();
}
