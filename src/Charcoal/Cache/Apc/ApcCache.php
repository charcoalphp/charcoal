<?php

namespace Charcoal\Cache\Apc;

use \Charcoal\Cache\AbstractCache as AbstractCache;

class ApcCache extends AbstractCache
{

    public function init()
    {
        return true;
    }

    /**
    * Wether APC cache is enabled or not / verify if the cache is properly set & configured.
    *
    * @return boolean True if enabled, false is disabled / inactive
    */
    public function enabled()
    {
        $active = $this->config()->active();
        if(!$active) {
            return false;
        }
        return !!extension_loaded('apc');
    }

    /**
    * Store the data in the cache.
    *
    * @param string $key The cache key where to store
    * @param mixed $data The data to store in the cache
    * @param integer $ttl Time-to-live, in seconds
    * @return boolean If storage was sucessful or not
    */
    public function store($key, $data, $ttl=0)
    {
        if(!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();
        $ttl = ($ttl > 0) ? $ttl : $this->default_ttl();

        return apc_store($prefix.$key, $data, $ttl);
    }

    /**
    * Verify if a key exists in the cache.
    *
    * @param string $key The cache key to verify
    * @return boolean True if the key exists, false if not
    */
    public function exists($key)
    {
        if(!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();
        
        $exists = false;
        apc_fetch($prefix.$key, $exists);
        return $exists;
    }

    /**
    * Fetch, or load, data from the cache.
    *
    * @param string $key The cache key to fetch
    * @return mixed The data that was stored in the cache.
    */
    public function fetch($key)
    {
        if(!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();

        return apc_fetch($prefix.$key);
    }

    /**
    * Fetch, or load, multiple keys at once from the cache.
    *
    * @param array $keys The cache keys to fetch
    * @return array The data, in an associatve array of `$key=>$data`
    */
    public function multifetch($keys)
    {
        if(!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();

        $pkeys = [];
        foreach($keys as $k) {
            $pkeys[] = $prefix.$k;
        }

        return apc_fetch($pkeys);
    }

    /**
    * Delete a key from the cache.
    *
    * @param string $key the cache key to delete
    * @return boolean True if delete was successful, false otherwise
    */
    public function delete($key)
    {
        if(!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();

        return apc_delete($prefix.$key);
    }

    /**
    * Completely clear the cache.
    *
    * @return boolean
    */
    public function clear()
    {
        if(!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();

        $keys = new \APCIterator('user', '/^'.$prefix.'/', APC_ITER_VALUE);
        return apc_delete($keys);
    }
}
