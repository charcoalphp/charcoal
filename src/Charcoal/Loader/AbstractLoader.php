<?php

namespace Charcoal\Loader;

use \Charcoal\Cache\CacheableInterface as CacheableInterface;
use \Charcoal\Cache\CacheableTrait as CacheableTrait;

use \Charcoal\Loader\LoaderInterface as LoaderInterface;

abstract class AbstractLoader implements
    LoaderInterface,
    CacheableInterface
{
    use CacheableTrait;
    
    /**
    * @var mixed $_content
    */
    protected $_content;

    /**
    * @param mixed $content
    * @return LoaderInterface Chainable
    */
    public function set_content($content)
    {
        $this->_content = $content;
        return $this;
    }

    /**
    * @return mixed
    */
    public function content()
    {
        return $this->_content;
    }

    /**
    * @param string|null $ident
    * @return mixed
    */
    abstract public function load($ident = null);
    
    /**
    * CacheableInterface > cache_data().
    *
    * @return mixed
    */
    public function cache_data()
    {
        return $this->content();
    }
}
