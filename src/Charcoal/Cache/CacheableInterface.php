<?php

namespace Charcoal\Cache;

use \Charcoal\Cache\CacheInterface as CacheInterface;

/**
*
*/
interface CacheableInterface
{
    /**
    * Set the object's Cache
    *
    * @param CacheInterface $cache
    * @return CacheableInterface Chainable
    */
    public function set_cache(CacheInterface $cache);

    /**
    * Get the object's Cache
    *
    * @return CacheInterface
    */
    public function cache();

    /**
    * Set the object's cache key
    *
    * @param string $cache_key
    * @return CacheableInterface Chainable
    */
    public function set_cache_key($cache_key);

    /**
    * Get the object's cache key
    *
    * @return string
    */
    public function cache_key();

    /**
    * Set the object's custom Time-To-Live in cache
    *
    * @param integer $ttl
    * @return CacheableInterface Chainable
    */
    public function set_cache_ttl($ttl);

    /**
    * @return integer
    */
    public function cache_ttl();

    /**
    * @param boolean $use_cache
    * @return CacheableInterface Chainable
    */
    public function set_use_cache($use_cache);

    /**
    * @return boolean
    */
    public function use_cache();

    /**
    * @return mixed
    */
    public function cache_data();

    /**
    * @param mixed   $data
    * @param integer $ttl
    * @return boolean
    */
    public function cache_store($data = null, $ttl = 0);

    /**
    * @return mixed
    */
    public function cache_load();
}
