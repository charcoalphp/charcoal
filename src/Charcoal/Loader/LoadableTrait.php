<?php

namespace Charcoal\Loader;

use \InvalidArgumentException;

/**
* Default implementation, as trait, of the `LoadableInterface`
*/
trait LoadableTrait
{
    /**
    * @var LoaderInterface $loader
    */
    protected $loader;

    /**
    * Set the loader object.
    *
    * @param array|LoaderInterface $loader
    * @throws InvalidArgumentException if loader is not an array or object
    * @return LoadableInterface Chainable
    */
    public function setLoader($loader)
    {
        if (is_array($loader)) {
            $this->loader = $this->createLoader($loader);
        } elseif (($loader instanceof LoaderInterface)) {
            $this->loader = $loader;
        } else {
            throw new InvalidArgumentException(
                'Loader must be an array or a Loader object.'
            );
        }
        return $this;
    }

    /**
    * Get the loader object.
    *
    * @return LoaderInterface
    */
    public function loader()
    {
        if ($this->loader === null) {
            $this->loader = $this->createLoader();
        }
        return $this->loader;
    }

    /**
    * @param array|null $data
    * @return LoaderInterface
    */
    abstract protected function createLoader($data = null);

    /**
    * @return mixed
    */
    public function load()
    {
        return $this->loader()->load();
    }
}
