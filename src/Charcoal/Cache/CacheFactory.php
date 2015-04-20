<?php

namespace Charcoal\Cache;

use \Charcoal\Core\AbstractFactory as AbstractFactory;

use \Charcoal\Cache\CacheInterface as CacheInterface;
use \Charcoal\Cache\Apc\ApcCache as ApcCache;
use \Charcoal\Cache\Memcache\MemcacheCache as MemcacheCache;
use \Charcoal\Cache\Noop\NoopCache as NoopCache;

class CacheFactory extends AbstractFactory
{

    /**
    * Get a cache instance from type
    *
    * @param string $type;
    * @throws \InvalidArgumentException if type is not a string or not a valid cache type
    * @return CacheInterface
    */
    public function get($type)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('Type (of cache) must be a string');
        }
        
        if ($type == 'apc') {
            $cache = ApcCache::instance();
        } else if ($type == 'memcache') {
            $cache = MemcacheCache::instance();
        } else if ($type == 'noop') {
            $cache = NoopCache::instance();
        } else {
            throw new \InvalidArgumentException('Type is not a valid cache type');
        }

        return $cache;
    }

    static public function types()
    {

        return array_merge(parent::types(), [
            'apc'       => '\Charcoal\Cache\Apc\ApcCache',
            'memcache'  => '\Charcoal\Cache\Memcache\MemcacheCache',
            'noop'      => '\Charcoal\Cache\Noop\NoopCache'
        ]);
    }
}
