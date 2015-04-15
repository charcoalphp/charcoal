<?php

namespace Charcoal\Loader;

interface LoaderInterface
{
    public function set_content($content);
    public function content();
    public function load($ident=null);
}
