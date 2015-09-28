<?php

namespace Charcoal\Loader;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Cache\CacheableInterface as CacheableInterface;
use \Charcoal\Cache\CacheableTrait as CacheableTrait;

// Local namespace dependencies
use \Charcoal\Loader\LoaderInterface as LoaderInterface;

/**
*
*/
abstract class AbstractLoader implements
    LoaderInterface,
    CacheableInterface
{
    use CacheableTrait;

    /**
    * @var mixed $content
    */
    protected $content;

    /**
    * @param mixed $content
    * @return LoaderInterface Chainable
    */
    public function set_content($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
    * @return mixed
    */
    public function content()
    {
        return $this->content;
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
