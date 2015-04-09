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
    * Keep a static copy of loaded caches
    *
    * @var array $_caches
    */
    static private $_caches = [];
    static private $_class_map = [];

    /**
    * Get a cache instance from type
    *
    * @param string $type;
    * @throws \InvalidArgumentException if type is not a string or not a valid cache type
    * @return CacheInterface
    */
    public function get($type)
    {
        if(!is_string($type)) {
            throw new \InvalidArgumentException('Type (of cache) must be a string');
        }
        if(isset(self::$_caches[$type]) && self::$_caches[$type] !== null) {
            return $this->_caches[$type];
        }
        if($type == 'apc') {
            $cache = new ApcCache();
        }
        else if($type == 'memcache') {
            $cache = new MemcacheCache();
        }
        else if($type == 'noop') {
            $cache = new NoopCache();
        }
        else {
            throw new \InvalidArgumentException('Type is not a valid cache type');
        }

        self::$_caches[$type] = $cache;
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
