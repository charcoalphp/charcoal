<?php

namespace Charcoal\View;

interface LoaderInterface
{
    /**
     * @param string $ident The template to load.
     * @return string
     */
    public function load($ident);
}
