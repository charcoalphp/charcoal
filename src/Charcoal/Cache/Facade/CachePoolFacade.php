<?php

namespace Charcoal\Cache\Facade;

/**
 * Cache layer to help deal with the cache.
 */
class CachePoolFacade
{

    /**
     * @var mixed $cache The cache pool.
     */
    private $cache;

    /**
     * @var mixed $logger Logger.
     */
    private $logger;

    /**
     * @var integer $ttl Cache time to live
     */
    private $ttl;

    /**
     * @param array $container Dependencies.
     */
    public function __construct(array $container)
    {
        $this->setCache($container['cache']);
        $this->setLogger($container['logger']);
        $this->setTtl($container['ttl'] ?: 60);
    }

    /**
     * Get cache object from key or sets it from lambda function.
     * @param  string        $key    Key to cache data.
     * @param  callable|null $lambda Function to generate cache data.
     * @param  integer|null  $ttl    Time to live.
     * @return mixed                    Whatever was in the cache.
     */
    public function get($key, $lambda = null, $ttl = null)
    {
        $cache     = $this->cache();
        $cacheItem = $cache->getItem($key);
        $out       = $cacheItem->get();

        if (!$cacheItem->isMiss()) {
            return $out;
        }

        if (is_callable($lambda)) {
            $out = call_user_func($lambda);
            $this->set($cacheItem, $out, $ttl);
        }

        return $out;
    }

    /**
     * Sets the cache data.
     * @param string $cacheItem Cache item.
     * @param mixed  $out       Data to set in cache.
     * @param mixed  $ttl       Time to live.
     * @return self
     */
    public function set($cacheItem, $out, $ttl = null)
    {
        $cacheItem->lock();

        if (!$ttl) {
            $ttl = $this->ttl();
        }

        $cache = $this->cache();

        $cacheItem->expiresAfter($ttl);
        $cacheItem->set($out);
        $cache->save($cacheItem);

        return $this;
    }

    /**
     * Removes the object from the cache.
     * @param  string $key Key to object.
     * @return self
     */
    public function delete($key)
    {
        $this->cache->deleteItem($key);

        return $this;
    }

    /**
     * @return mixed
     */
    public function cache()
    {
        return $this->cache;
    }

    /**
     * @param mixed $cache Cache for CachePoolFacade.
     * @return self
     */
    public function setCache($cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return mixed
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * @param mixed $logger Logger for CachePoolFacade.
     * @return self
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return mixed
     */
    public function ttl()
    {
        return $this->ttl;
    }

    /**
     * @param mixed $ttl Cache time to live.
     * @return self
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }
}
