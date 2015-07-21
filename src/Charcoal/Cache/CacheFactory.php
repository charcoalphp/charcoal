<?php

namespace Charcoal\Cache;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\AbstractFactory as AbstractFactory;

// Local namespace dependencies
use \Charcoal\Cache\CacheInterface as CacheInterface;
use \Charcoal\Cache\Apc\ApcCache as ApcCache;
use \Charcoal\Cache\Memcache\MemcacheCache as MemcacheCache;
use \Charcoal\Cache\Noop\NoopCache as NoopCache;

/**
* Cache factory
*/
class CacheFactory extends AbstractFactory
{
    /**
    * Get a cache instance from type
    *
    * @param string $type
    * @throws InvalidArgumentException if type is not a string or not a valid cache type
    * @return CacheInterface
    */
    public function get($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Type (of cache) must be a string.');
        }

        if ($type == 'apc') {
            $cache = ApcCache::instance();
        } elseif ($type == 'memcache') {
            $cache = MemcacheCache::instance();
        } elseif ($type == 'noop') {
            $cache = NoopCache::instance();
        } else {
            throw new InvalidArgumentException(sprintf('Type "%s" is not a valid cache type.', $type));
        }

        return $cache;
    }
}
