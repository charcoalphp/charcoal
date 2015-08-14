<?php

namespace Charcoal\Cache;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\ClassMapFactory as ClassMapFactory;

// Local namespace dependencies
use \Charcoal\Cache\CacheInterface as CacheInterface;
use \Charcoal\Cache\Apc\ApcCache as ApcCache;
use \Charcoal\Cache\Memcache\MemcacheCache as MemcacheCache;
use \Charcoal\Cache\Noop\NoopCache as NoopCache;

/**
* Cache factory
*/
class CacheFactory extends ClassMapFactory
{
    /**
    * @param array|null $data
    */
    public function __construct(array $data = null)
    {
        $this->set_base_class('\Charcoal\Cache\CacheInterface');
        $this->set_class_map([
            'apc'=>'\Charcoal\Cache\Apc\ApcCache',
            'memcache'=>'\Charcoal\Cache\Memcache\MemcacheCache',
            'noop'=>'\Charcoal\Cache\Noop\NoopCache'
        ]);

        if ($data !== null) {
            $this->set_data($data);
        }
    }
}
