<?php

namespace Charcoal\Cache\Facade;

// From PSR-6
use Psr\Cache\CacheItemInterface;
// From 'charcoal-cache'
use Charcoal\Cache\CacheConfig;
use Charcoal\Cache\CachePoolAwareTrait;

/**
 * Cache Pool Facade
 *
 * The facade provides a simpler interface to work with the cache pool.
 */
class CachePoolFacade
{
    use CachePoolAwareTrait;

    /**
     * Default maximum time an item will be cached.
     *
     * Either an integer, date interval, or date.
     *
     * @var mixed
     */
    private $defaultTtl = CacheConfig::HOUR_IN_SECONDS;

    /**
     * Create a cache pool facade.
     *
     * @param array $data The facade dependencies.
     */
    public function __construct(array $data)
    {
        $this->setCachePool($data['cache']);

        if (isset($data['default_ttl'])) {
            $this->setDefaultTtl($data['default_ttl']);
        }
    }

    /**
     * Retrieve the value associated with the specified key from the pool.
     *
     * This method will call $lambada if the cache item representing $key resulted in a cache miss.
     *
     * @param  string        $key     The key for which to return the associated value.
     * @param  callable|null $resolve The function to execute if the cached value does not exist
     *     or is considered expired. The function must return a value which will be stored
     *     in the cache before being returned by the method.
     *
     *     ```
     *     $resolve ( mixed $data, CacheItemInterface $item ) : mixed
     *     ```
     *
     *     The $resolve takes on two parameters:
     *
     *     1. The expired value or NULL if no value was stored.
     *     2. The cache item of the specified key.
     * @param  mixed         $ttl     An integer, interval, date, or NULL to use the facade's default value.
     * @return mixed The value corresponding to this cache item's $key, or NULL if not found.
     */
    public function get($key, callable $resolve = null, $ttl = null)
    {
        $pool = $this->cachePool();
        $item = $pool->getItem($key);
        $data = $item->get();

        if ($item->isHit()) {
            return $data;
        }

        if (is_callable($resolve)) {
            $data = $resolve($data, $item);
            $this->save($item, $data, $ttl);
            return $data;
        }

        return null;
    }

    /**
     * Determine if the specified key results in a cache hit.
     *
     * @param  string $key The key for which to check existence.
     * @return boolean TRUE if item exists in the cache, FALSE otherwise.
     */
    public function has($key)
    {
        return $this->cachePool()->getItem($key)->isHit();
    }

    /**
     * Store a value with the specified key to be saved immediately.
     *
     * @param  string $key   The key to save the value on.
     * @param  mixed  $value The serializable value to be stored.
     * @param  mixed  $ttl   An integer, interval, date, or NULL to use the facade's default value.
     * @return boolean TRUE if the item was successfully persisted. FALSE if there was an error.
     */
    public function set($key, $value, $ttl = null)
    {
        $item = $this->cachePool()->getItem($key);

        return $this->save($item, $value, $ttl);
    }

    /**
     * Set a value on a cache item to be saved immediately.
     *
     * @param  CacheItemInterface $item  The cache item to save.
     * @param  mixed              $value The serializable value to be stored.
     * @param  mixed              $ttl   An integer, interval, date, or NULL to use the facade's default value.
     * @return boolean TRUE if the item was successfully persisted. FALSE if there was an error.
     */
    protected function save(CacheItemInterface $item, $value, $ttl = null)
    {
        if ($ttl === null) {
            $ttl = $this->defaultTtl();
        }

        if (is_numeric($ttl) || ($ttl instanceof \DateInterval)) {
            $item->expiresAfter($ttl);
        } elseif ($ttl instanceof \DateTimeInterface) {
            $item->expiresAt($ttl);
        }

        $item->set($value);

        return $this->cachePool()->save($item);
    }

    /**
     * Removes one or more items from the pool.
     *
     * @param  string ...$keys One or many keys to delete.
     * @return bool TRUE if the item was successfully removed. FALSE if there was an error.
     */
    public function delete(...$keys)
    {
        $pool = $this->cachePool();

        $results = true;
        foreach ($keys as $key) {
            $results = $pool->deleteItem($key) && $results;
        }

        return $results;
    }

    /**
     * Retrieve the facade's default time-to-live for cached items.
     *
     * @return mixed An integer, date interval, or date.
     */
    public function defaultTtl()
    {
        return $this->defaultTtl;
    }

    /**
     * Set the facade's default time-to-live for cached items.
     *
     * @param  mixed $ttl An integer, date interval, or date.
     * @return void
     */
    public function setDefaultTtl($ttl)
    {
        $this->defaultTtl = $ttl;
    }
}
