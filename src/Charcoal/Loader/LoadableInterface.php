<?php

namespace \Charcoal\Loader;

interface LoadableInterface
{
    public function set_loader(LoaderInterface $loader);
    public function loader();
    public function load();
}
