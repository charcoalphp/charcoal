<?php

namespace Charcoal\Loader;

// Local namespace dependencies
use \Charcoal\Loader\LoaderInterface as LoaderInterface;

/**
*
*/
abstract class AbstractLoader implements
    LoaderInterface
{

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
}
