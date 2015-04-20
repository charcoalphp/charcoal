<?php

namespace Charcoal\Loader;

trait LoadableTrait
{
    protected $_loader;

    /**
    * Set the loader object.
    *
    * @param LoaderInterface $loader
    * @return LoadableInterface Chainable
    */
    public function set_loader(LoaderInterface $loader)
    {
        $this->_loader = $loader;
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
    * @return LoaderInterface
    */
    abstract protected function create_loader($data=null);

    /**
    *
    */
    public function load()
    {
        return $this->loader()->load();
    }
}
