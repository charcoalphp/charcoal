<?php

namespace Charcoal\Cache;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Config\ConfigurableInterface as ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait as ConfigurableTrait;

use \Charcoal\Cache\CacheConfig as CacheConfig;
use \Charcoal\Cache\CacheInterface as CacheInterface;

/**
* An abstract class that fulfills the full CacheInterface
*
* This class also implements `ConfigurableInterface`. It does so by using the
* `ConfigurableTrait` and subclassing the `create_config()` and `_config_from_array()` methods.
*/
abstract class AbstractCache implements
    CacheInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
    * Singleton instance
    */
    static protected $_instance;

    /**
    * @var string
    */
    private $_prefix;

    /**
    *
    */
    protected function __construct()
    {
        // Protected
        $this->init();
    }

    /**
    * Static singleton instance getter
    * @return CacheInterface
    */
    static public function instance()
    {
        if (static::$_instance !== null) {
            return static::$_instance;
        }
        $class = get_called_class();
        return new $class;
    }

    /**
    * Sets the global cache prefix.
    *
    * Set this to ensure multiple Charcoal project on the same cache servers do not overwrite eachother's cache.
    *
    * @param string $prefix The hard-coded prefix
    * @throws InvalidArgumentException if prefix is not a string
    * @return CacheInterface Chainable
    */
    public function set_prefix($prefix)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException('Prefix must be a string');
        }
        $this->_prefix = $prefix;
        return $this;
    }

    /**
    * Gets the prefix. Guess from configuration if none was previously set.
    *
    * @return string The cache prefix
    */
    public function prefix()
    {
        if ($this->_prefix === null) {
            return $this->config()->prefix();
        }
        return $this->_prefix;
    }

    /**
    * Wether this cache is enabled or not / verify if the cache is properly set & configured.
    *
    * @return boolean True if enabled, false is disabled / inactive
    */
    public function enabled()
    {
        return $this->config()->active();
    }

    /**
    * Initialize cache
    *
    * @return boolean
    */
    abstract public function init();

    /**
    * Store the data in the cache.
    *
    * @param string  $key  The cache key where to store
    * @param mixed   $data The data to store in the cache
    * @param integer $ttl  Time-to-live, in seconds
    * @return boolean If storage was sucessful or not
    */
    abstract public function store($key, $data, $ttl = 0);

    /**
    * Verify if a key exists in the cache.
    *
    * @param string $key The cache key to verify
    * @return boolean True if the key exists, false if not
    */
    abstract public function exists($key);

    /**
    * Fetch, or load, data from the cache.
    *
    * @param string $key The cache key to fetch
    * @return mixed The data that was stored in the cache.
    */
    abstract public function fetch($key);

    /**
    * Fetch, or load, multiple keys at once from the cache.
    *
    * @param array $keys An array of cache keys to fetch
    * @return array  The data, in an associatve array of `$key=>$data`
    */
    abstract public function multifetch($keys);

    /**
    * Delete a key from the cache.
    *
    * @param string $key the cache key to delete
    * @return boolean True if delete was successful, false otherwise
    */
    abstract public function delete($key);

    /**
    * Completely clear the cache.
    *
    * @return boolean
    */
    abstract public function clear();

    /**
    * ConfigurableInterface > create_config() implementation.
    *
    * @param array $data
    * @return ConfigInterface
    */
    protected function create_config($data = null)
    {
        $config = new CacheConfig();
        if ($data !== null) {
            $config->set_data($data);
        }
        return $config;
    }
}
