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
    */
    public function set_content($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function content()
    {
        return $this->_content;
    }

    abstract public function load($ident = null);
    
    /**
    * CacheableInterface > cache_data().
    */
    public function cache_data()
    {
        return $this->content();
    }
}
