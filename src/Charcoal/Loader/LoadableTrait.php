<?php

namespace Charcoal\Loader;

/**
* Default implementation, as trait, of the `LoadableInterface`
*/
trait LoadableTrait
{
    /**
    * @var LoaderInterface $_loader
    */
    protected $_loader;

    /**
    * Set the loader object.
    *
    * @param array|LoaderInterface $loader
    * @throws \InvalidArgumentException if loader is not an array or object
    * @return LoadableInterface Chainable
    */
    public function set_loader($loader)
    {
        if (is_array($loader)) {
            $this->_loader = $this->create_loader($loader);
        } else if (($loader instanceof LoaderInterface)) {
            $this->_loader = $loader;
        } else {
            throw new \InvalidArgumentException('Loader must be an array or a Loader object.');
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
        if ($this->_loader === null) {
            $this->_loader = $this->create_loader();
        }
        return $this->_loader;
    }

    /**
    * @param array|null $data
    * @return LoaderInterface
    */
    abstract protected function create_loader($data = null);

    /**
    * @return mixed
    */
    public function load()
    {
        return $this->loader()->load();
    }
}
