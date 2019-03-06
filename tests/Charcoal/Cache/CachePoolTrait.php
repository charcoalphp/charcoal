<?php

namespace Charcoal\Tests\Cache;

// From PSR-6
use Psr\Cache\CacheItemPoolInterface;

// From 'tedivm/stash'
use Stash\Driver\Ephemeral;
use Stash\Pool;

/**
 * Cache Pool Helper for Test Cases
 */
trait CachePoolTrait
{
    /**
     * PSR-6 cache item pool.
     *
     * @var CacheItemPoolInterface
     */
    protected static $cachePool;

    /**
     * Create the cache pool service.
     *
     * @return CacheItemPoolInterface
     */
    protected static function createCachePool()
    {
        $pool = new Pool(new Ephemeral());
        $pool->setNamespace('tests');

        self::$cachePool = $pool;

        return $pool;
    }

    /**
     * Gets the cache pool service.
     *
     * @return void
     */
    protected static function clearCachePool()
    {
        if (self::$cachePool !== null) {
            self::$cachePool->clear();
        }
    }

    /**
     * Gets the cache pool service.
     *
     * @return CacheItemPoolInterface
     */
    protected static function getCachePool()
    {
        if (self::$cachePool === null) {
            static::createCachePool();
        }

        return self::$cachePool;
    }
}
