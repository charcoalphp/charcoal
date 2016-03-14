<?php

namespace Charcoal\Loader;

/**
 * Obsolete.
 */
interface LoaderInterface
{

    /**
     * @param  string|null $ident The ident to load.
     * @return mixed
     */
    public function load($ident = null);
}
