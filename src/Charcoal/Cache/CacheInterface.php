<?php

namespace Charcoal\Cache;

/**
*
*/
interface CacheInterface
{
    /**
    * Sets the global cache prefix.
    *
    * Set this to ensure multiple Charcoal project on the same cache servers do not overwrite eachother's cache.
    *
    * @param string $prefix The hard-coded prefix
    * @return CacheInterface Chainable
    */
    public function set_prefix($prefix);

    /**
    * Gets the prefix. Guess from configuration if none was previously set.
    *
    * @return string The cache prefix
    */
    public function prefix();

    /**
    * Wether this cache is enabled or not / verify if the cache is properly set & configured.
    *
    * @return boolean True if enabled, false is disabled / inactive
    */
    public function enabled();

    /**
    * Initialize cache
    *
    * @return boolean
    */
    public function init();

    /**
    * Store the data in the cache.
    *
    * @param string $key The cache key where to store
    * @param mixed $data The data to store in the cache
    * @param integer $ttl Time-to-live, in seconds
    * @return boolean If storage was sucessful or not
    */
    public function store($key, $data, $ttl = 0);

    /**
    * Verify if a key exists in the cache.
    *
    * @param string $key The cache key to verify
    * @return boolean True if the key exists, false if not
    */
    public function exists($key);

    /**
    * Fetch, or load, data from the cache.
    *
    * @param string $key The cache key to fetch
    * @return mixed The data that was stored in the cache. Null if non-existent.
    */
    public function fetch($key);

    /**
    * Fetch, or load, multiple keys from the cache
    *
    * @param array $keys An array of cache keys to fetch
    * @return array  The data, in an associatve array of `$key=>$data`
    */
    public function multifetch($keys);

    /**
    * Delete a key from the cache.
    *
    * @param string $key the cache key to delete
    * @return boolean True if delete was successful, false otherwise
    */
    public function delete($key);

    /**
    * Completely clear the cache.
    *
    * @return boolean
    */
    public function clear();
}
