<?php

namespace Charcoal\Cache\Memcache;

use \Memcache as Memcache;

use \Charcoal\Cache\AbstractCache as AbstractCache;

use \Charcoal\Cache\Memcache\MemcacheCacheConfig as MemcacheCacheConfig;
use \Charcoal\Cache\Memcache\MemCacheCacheServerConfig as MemCacheCacheServerConfig;

/**
* A Charcoal Cache implementation using Memcache as backend
*/
class MemcacheCache extends AbstractCache
{
    private $_enabled;
    private $_memcache;

    public function init()
    {
        if(!$this->enabled()) {
            return false;
        }
        
        $cfg = $this->config();

        $this->_memcache = new Memcache();
        $servers = $cfg->servers;
        if(count($servers) == 0) {
            throw new \Exception('Memcache: no server(s) defined');
        }
        foreach($cfg->servers() as $s) {
            $this->_add_server($s);
        }
        return $this;
    }

    /**
    * Wether memcache is enabled or not / verify if the cache is properly set & configured.
    *
    * @return boolean True if enabled, false is disabled / inactive
    */
    public function enabled()
    {
        if($this->_enabled !== null) {
            return $this->_enabled;
        }
        $active = $this->config()->active();
        if(!$active) {
            $this->_enabled = false;
            return true;
        }
        $this->_enabled = !!class_exists('Memcache');
        return $this->_enabled;
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

        $flag = 0; // MEMCACHE_COMPRESSED
        return $this->_memcache->set($prefix.$key, $data, $flag, $ttl);
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
        
        return !!$this->_memcache->get($prefix.$key);
    }

    /**
    * Fetch, or load, data from the cache.
    *
    * @param string $key The cache key to fetch
    * @return mixed The data that was stored in the cache. Null if non-existent.
    */
    public function fetch($key)
    {
        if(!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();
        
        return $this->_memcache->get($prefix.$key);
    }

    /**
    * Fetch, or load, data from the cache.
    *
    * @param string $key The cache key to fetch
    * @return mixed The data that was stored in the cache. Null if non-existent.
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
        return $this->_memcache->getMulti($pkeys);
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

        return $this->_memcache->delete($prefix.$key);
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

        $this->_memcache->flush();
        // Memcache invalidates any cache in th same second as the flush.
        // So sleep for 1 full second to ensure nothing will be lost.
        sleep(1);
        return true;
    }

    /**
    * @param array|MemcacheCacheServerConfig $server
    * @throws \InvalidArgumentException if server is not a proper array or object
    * @return boolean
    */
    public function add_server($server)
    {
        if(is_array($server)) {
            $server = new MemCacheCacheServerConfig($server);
        }
        if(!($server instanceof MemCacheCacheServerConfig)) {
            throw new \InvalidArgumentException('Invalid server');
        }
        $hostname = $server->hostname();
        $port = $server->port();
        $persistent = $server->persistent();
        $weight = $server->weight();

        return $this->_memcache->addServer($hostname, $port, $persistent, $weight);
    }

    /**
    * ConfigurableInterface > config()
    */
    public function config()
    {
        if($this->_config === null) {
            $this->_config = new MemcacheCacheConfig();
        }
        return $this->_config;
    }

    protected function _config_from_array($config)
    {
        $config = new MemcacheCacheConfig($config);
        return $config;
    }
}
