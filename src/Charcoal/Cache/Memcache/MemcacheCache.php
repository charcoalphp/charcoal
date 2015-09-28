<?php

namespace Charcoal\Cache\Memcache;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// Dependencies from PHP extensions
use \Memcache as Memcache;

// Local parent namespace dependencies
use \Charcoal\Cache\AbstractCache as AbstractCache;

// Local namespace dependencies
use \Charcoal\Cache\Memcache\MemcacheCacheConfig as MemcacheCacheConfig;
use \Charcoal\Cache\Memcache\MemCacheCacheServerConfig as MemCacheCacheServerConfig;

/**
* A Charcoal Cache implementation using Memcache as backend
*/
class MemcacheCache extends AbstractCache
{
    /**
    * @var boolean|null $enabled
    */
    private $enabled = null;
    /**
    * Copy ot the Memcache object
    * @var Memcache|null $memcache
    */
    private $memcache = null;

    /**
    * @throws Exception
    * @return MemcacheCache|false
    */
    public function init()
    {
        if (!$this->enabled()) {
            return false;
        }

        $cfg = $this->config();

        $this->memcache = new Memcache();
        $servers = $cfg->servers();
        if (count($servers) == 0) {
            throw new Exception('Memcache: no server(s) defined.');
        }
        foreach ($cfg->servers() as $s) {
            $srv = $this->add_server($s);
            if ($srv === false) {
                throw new Exception('Memcache: could not add server.');
            }
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
        if ($this->config()->active() === false) {
            return false;
        }
        if ($this->enabled === null) {
            $this->enabled = !!class_exists('Memcache');
        }

        return $this->enabled;
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
        if (!$this->enabled()) {
            return false;
        }

        $prefix = $this->prefix();
        $ttl = (($ttl > 0) ? $ttl : $this->config()->default_ttl());

        $flag = 0; // MEMCACHE_COMPRESSED

        return $this->memcache->set($prefix.$key, $data, $flag, $ttl);
    }

    /**
    * Verify if a key exists in the cache.
    *
    * @param string $key The cache key to verify
    * @return boolean True if the key exists, false if not
    */
    public function exists($key)
    {
        if (!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();

        return !!$this->memcache->get($prefix.$key);
    }

    /**
    * Fetch, or load, data from the cache.
    *
    * @param string $key The cache key to fetch
    * @return mixed The data that was stored in the cache. Null if non-existent.
    */
    public function fetch($key)
    {
        if (!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();

        return $this->memcache->get($prefix.$key);
    }

    /**
    * Fetch, or load, data from the cache.
    *
    * @param array $keys The cache keys to fetch
    * @return mixed The data that was stored in the cache. Null if non-existent.
    */
    public function multifetch($keys)
    {
        if (!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();

        $pkeys = [];
        foreach ($keys as $k) {
            $pkeys[] = $prefix.$k;
        }
        return $this->memcache->get($pkeys);
    }

    /**
    * Delete a key from the cache.
    *
    * @param string $key the cache key to delete
    * @return boolean True if delete was successful, false otherwise
    */
    public function delete($key)
    {
        if (!$this->enabled()) {
            return false;
        }
        $prefix = $this->prefix();

        return $this->memcache->delete($prefix.$key);
    }

    /**
    * Completely clear the cache.
    *
    * Warning: This function sleeps for 1 second.
    *
    * @return boolean
    */
    public function clear()
    {
        if (!$this->enabled()) {
            return false;
        }

        $this->memcache->flush();
        // Memcache invalidates any cache in th same second as the flush.
        // So sleep for 1 full second to ensure nothing will be lost.
        sleep(1);
        return true;
    }

    /**
    * Add a server to the server pool.
    *
    * Note that this method does *not* check if a server is valid.
    * To test validity of a server, use `test_server()`
    *
    * @param array|MemcacheCacheServerConfig $server
    * @throws InvalidArgumentException If server is not a proper array or object.
    * @throws Exception If the server is invalid.
    * @return boolean
    */
    public function add_server($server)
    {
        if (is_array($server)) {
            $server = new MemCacheCacheServerConfig($server);
        }
        if (!($server instanceof MemCacheCacheServerConfig)) {
            throw new InvalidArgumentException('Invalid server.');
        }
        if ($this->test_server($server) === false) {
            throw new Exception('Memcache: impossible to connect to server.');
        }

        $host = $server->host();
        $port = $server->port();
        $persistent = $server->persistent();
        $weight = $server->weight();

        return $this->memcache->addServer($host, $port, $persistent, $weight);
    }

    /**
    * Return wether a server is valid (can be connected to).
    *
    * @param array|MemcacheCacheServerConfig $server
    * @throws InvalidArgumentException if server is not a proper array or object
    * @return boolean
    */
    public function test_server($server)
    {
        if (is_array($server)) {
            $server = new MemCacheCacheServerConfig($server);
        }
        if (!($server instanceof MemCacheCacheServerConfig)) {
            throw new InvalidArgumentException('Invalid server.');
        }

        $host = $server->host();
        $port = $server->port();
        set_error_handler(function($err_no, $err_str) {
            unset($err_no, $err_str);

        });
        $res = $this->memcache->connect($host, $port);
        restore_error_handler();
        return $res;

    }

    /**
    * ConfigurableInterface > create_config()
    *
    * @param array $data Optional
    * @return MemcacheCacheConfig
    */
    public function create_config(array $data = null)
    {
        $config = new MemcacheCacheConfig();
        if (is_array($data)) {
            $config->set_data($data);
        }

        return $config;
    }
}
