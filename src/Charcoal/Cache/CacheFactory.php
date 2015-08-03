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
    * @param array|null $data
    */
    public function __construct(array $data = null)
    {
        $this->set_factory_mode(AbstractFactory::MODE_CLASS_MAP);
        $this->set_base_class('\Charcoal\Cache\CacheInterface');
        $this->set_types([
            'apc'=>'\Charcoal\Cache\Apc\ApcCache',
            'memcache'=>'\Charcoal\Cache\Memcache\MemcacheCache',
            'noop'=>'\Charcoal\Cache\Noop\NoopCache'
        ]);

        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    * Get a cache instance from type
    *
    * @param string $type
    * @throws InvalidArgumentException if type is not a string or not a valid cache type
    * @return CacheInterface
    */
    public function create($type)
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

    /*
    * Because the cache object is a singleton, get is exactly the same as create.
    *
    * @param string $type
    * @throws InvalidArgumentException
    * @return CacheInterface
    */
    public function get($type)
    {
        return $this->create($type);
    }
}
