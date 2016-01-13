<?php

namespace Charcoal\Loader;

/**
*
*/
interface LoaderInterface
{

    /**
    * @param string|null $ident
    * @return mixed
    */
    public function load($ident = null);
}
