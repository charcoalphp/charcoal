<?php

namespace Charcoal\Loader;

use \Charcoal\Cache\CacheableInterface as CacheableInterface;

use \Charcoal\Loader\LoaderInterface as LoaderInterface;

abstract class AbstractLoader implements
    LoaderInterface,
    CacheableInterface
{
    use CacheableTrait;
}
