<?php

namespace Charcoal\Loader;

interface LoaderInterface
{
    /**
    * @param mixed $content
    * @return LoaderInterface Chainable
    */
    public function set_content($content);

    /**
    * @return mixed
    */
    public function content();

    /**
    * @param string|null $ident
    * @return mixed
    */
    public function load($ident = null);
}
