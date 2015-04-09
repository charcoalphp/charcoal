<?php

namespace Charcoal\Cache;

trait CacheableTrait
{
    private $_cache;
    private $_cache_key;
    private $_cache_ttl;
    private $_use_cache = true;

    /**
    * Set the object's Cache
    *
    * @param CacheInterface $cache
    * @return CacheableInterface Chainable
    */
    public function set_cache(CacheInterface $cache)
    {
        $this->_cache = $cache;
        return $this;
    }

    /**
    * Get the object's Cache
    *
    * @return CacheInterface
    */
    public function cache()
    {
        return $this->_cache;
    }

    /**
    * Set the object's cache key
    *
    * @param string
    * @throws \InvalidArgumentException if cache key is not a string
    * @return CacheableInterface Chainable
    */
    public function set_cache_key($cache_key)
    {
        if(!is_string($cache_key)) {
            throw new \InvalidArgumentException('Cache key must be a string');
        }
        $this->_cache_key = $cache_key;
        return $this;
    }

    /**
    * Get the object's cache key
    *
    * @return string
    */
    public function cache_key()
    {
        return $this->_cache_key;
    }

    /**
    * Set the object's custom Time-To-Live in cache
    *
    * @param integer $ttl
    * @return CacheableInterface Chainable
    */
    public function set_cache_ttl($ttl)
    {
        $this->_cache_ttl = $ttl;
        return $this;
    }

    /**
    * @return integer
    */
    public function cache_ttl()
    {
        if($this->_cache_ttl === null) {
            return $this->cache()->default_ttl();
        }
        return $this->_cache_ttl;
    }

    /**
    * @param boolean $use_cache
    * @throws \InvalidArgumentException if use_cache is not a boolean
    * @return CacheableInterface Chainable
    */
    public function set_use_cache($use_cache)
    {
        if(!is_bool($use_cache)) {
            throw new \InvalidArgumentException('Use cache must be a boolean');
        }
        $this->_use_cache = $use_cache;
        return $this;
    }

    /**
    * @return boolean
    */
    public function use_cache()
    {
        return ($this->_use_cache && $this->cache()->enabled());
    }

    /**
    * @return mixed
    */
    abstract public function cache_data();

    /**
    * @param integer $ttl
    * @return boolean
    */
    public function cache_store($ttl=0)
    {
        $key = $this->cache_key();
        $data = $this->cache_data();
        $ttl = ($ttl > 0) ? $ttl : $this->cache_ttl();

        return $this->cache()->store($key, $data, $ttl);
    }

    /**
    * @return mixed
    */
    public function cache_load()
    {
        $key = $this->cache_key();
        return $this->cache()->fetch($key);
    }
}
