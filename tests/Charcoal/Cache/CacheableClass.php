<?php

namespace Charcoal\Tests\Cache;

use \Charcoal\Cache\CacheableInterface as CacheableInterface;
use \Charcoal\Cache\CacheableTrait as CacheableTrait;

/**
* Concrete implementation of CacheableTrait for Unit Tests.
*/
class CacheableClass implements CacheableInterface
{
    use CacheableTrait;

    public function cache_data()
    {
        return '';
    }
}
