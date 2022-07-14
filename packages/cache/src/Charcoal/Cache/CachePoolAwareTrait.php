<?php

namespace Charcoal\Cache;

use RuntimeException;
// From PSR-6
use Psr\Cache\CacheItemPoolInterface;

/**
 * The Cache Aware Trait provides the methods necessary for an object
 * to use a "Cache" service.
 */
trait CachePoolAwareTrait
{
    /**
     * Store the PSR-6 caching service.
     *
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * Set the cache pool manager.
     *
     * @param  CacheItemPoolInterface $cache A PSR-6 compliant cache pool instance.
     * @return void
     */
    protected function setCachePool(CacheItemPoolInterface $cache)
    {
        $this->cachePool = $cache;
    }

    /**
     * Retrieve the cache service.
     *
     * @throws RuntimeException If the cache service was not previously set.
     * @return CacheItemPoolInterface
     */
    protected function cachePool()
    {
        if ($this->cachePool === null) {
            throw new RuntimeException(sprintf(
                'Cache Pool is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->cachePool;
    }
}
