<?php

namespace Charcoal\Cache\Noop;

// Local parent namespace dependencies
use \Charcoal\Cache\AbstractCache as AbstractCache;

/**
* A Charcoal Cache implementations using a null backend.
*
* This class allows to use the cache system seamlessly but disregard all cache.
* > ⚠ Obviously, the noop cache should not be used in situation where having data in cache is actually crucial.
*/
class NoopCache extends AbstractCache
{
    /**
    * @return boolean
    */
    public function init()
    {
        return true;
    }

    /**
    * Store the data in the cache.
    *
    * @param string  $key  The cache key where to store
    * @param mixed   $data The data to store in the cache
    * @param integer $ttl  Time-to-live, in seconds
    * @return boolean If storage was sucessful or not
    */
    public function store($key, $data, $ttl = 0)
    {
        return true;
    }

    /**
    * Verify if a key exists in the cache.
    *
    * @param string $key The cache key to verify
    * @return boolean True if the key exists, false if not
    */
    public function exists($key)
    {
        return false;
    }

    /**
    * Fetch, or load, data from the cache.
    *
    * @param string $key The cache key to fetch
    * @return mixed The data that was stored in the cache. Null if non-existent.
    */
    public function fetch($key)
    {
        return false;
    }

    /**
    * @param array $keys
    * @return boolean
    */
    public function multifetch($keys)
    {
        return false;
    }

    /**
    * Delete a key from the cache.
    *
    * @param string $key the cache key to delete
    * @return boolean True if delete was successful, false otherwise
    */
    public function delete($key)
    {
        return true;
    }

    /**
    * Completely clear the cache.
    *
    * @return boolean
    */
    public function clear()
    {
        return true;
    }
}
