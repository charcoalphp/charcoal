<?php

namespace Charcoal\Cache;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Cache Configuration
 */
class CacheConfig extends AbstractConfig
{
    public const DEFAULT_NAMESPACE = 'charcoal';

    /**
     * Default cache type and fallback for user preference.
     */
    public const DEFAULT_TYPES = [
        'memory' => true
    ];

    /**
     * Human-readable intervals in seconds.
     */
    public const HOUR_IN_SECONDS = 3600;
    public const DAY_IN_SECONDS  = 86400;
    public const WEEK_IN_SECONDS = 604800;

    /**
     * Whether to enable or disable the cache service.
     *
     * Note:
     * - When TRUE, the {@see self::$types} are used.
     * - When FALSE, the "memory" type is used.
     *
     * @var boolean
     */
    private $active = true;

    /**
     * Cache type(s) to use.
     *
     * Represents a cache driver.
     *
     * @var array
     */
    private $types;

    /**
     * Default maximum time an item will be cached.
     *
     * @var integer
     */
    private $defaultTtl = self::WEEK_IN_SECONDS;

    /**
     * Cache namespace.
     *
     * @var string
     */
    private $prefix = self::DEFAULT_NAMESPACE;

    /**
     * Retrieve the default values.
     *
     * @return array
     */
    public function defaults()
    {
        return [
            'active'      => true,
            'types'       => $this->defaultTypes(),
            'default_ttl' => self::WEEK_IN_SECONDS,
            'prefix'      => self::DEFAULT_NAMESPACE
        ];
    }

    /**
     * Enable / Disable the cache service.
     *
     * @param  boolean $active The active flag;
     *     TRUE to enable, FALSE to disable.
     * @return CacheConfig Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
     * Determine if the cache service is enabled.
     *
     * @return boolean TRUE if enabled, FALSE if disabled.
     */
    public function active()
    {
        return $this->active;
    }

    /**
     * Set the cache type(s) to use.
     *
     * The first cache actually available on the system will be the one used for caching.
     *
     * @param  string[] $types One or more types to try as cache driver until success.
     * @return CacheConfig Chainable
     */
    public function setTypes(array $types)
    {
        $this->types = [];
        $this->addTypes($types);
        return $this;
    }

    /**
     * Add cache type(s) to use.
     *
     * @param  string[] $types One or more types to try as cache driver until success.
     * @return CacheConfig Chainable
     */
    public function addTypes(array $types)
    {
        foreach ($types as $type) {
            $this->addType($type);
        }
        return $this;
    }

    /**
     * Add a cache type to use.
     *
     * @param  string $type The cache type.
     * @throws InvalidArgumentException If the type is not a string or unsupported.
     * @return CacheConfig Chainable
     */
    public function addType($type)
    {
        if (!in_array($type, $this->validTypes())) {
            throw new InvalidArgumentException(
                sprintf('Invalid cache type: "%s"', $type)
            );
        }

        $this->types[$type] = true;
        return $this;
    }

    /**
     * Retrieve the cache type(s) to use.
     *
     * Note:
     * 1. The default cache type is always appended.
     * 2. Duplicate types are removed.
     *
     * @return array
     */
    public function types()
    {
        $types = ($this->types + self::DEFAULT_TYPES);
        return array_keys($types);
    }

    /**
     * Retrieve the default cache types.
     *
     * @return string[]
     */
    public function defaultTypes()
    {
        return array_keys(self::DEFAULT_TYPES);
    }

    /**
     * Retrieve the available cache types.
     *
     * @return string[]
     */
    public function validTypes()
    {
        return [
            'apc',
            'file',
            'db',
            'memcache',
            'memory',
            'noop',
            'redis'
        ];
    }

    /**
     * Set the default time-to-live for cached items.
     *
     * @param  mixed $ttl A number representing time in seconds.
     * @throws InvalidArgumentException If the TTL is not numeric.
     * @return CacheConfig Chainable
     */
    public function setDefaultTtl($ttl)
    {
        if (!is_numeric($ttl)) {
            throw new InvalidArgumentException(
                'TTL must be an integer (seconds)'
            );
        }

        $this->defaultTtl = intval($ttl);
        return $this;
    }

    /**
     * Retrieve the default time-to-live for cached items.
     *
     * @return integer
     */
    public function defaultTtl()
    {
        return $this->defaultTtl;
    }

    /**
     * Set the cache namespace.
     *
     * @param  string $prefix The cache prefix (or namespace).
     * @throws InvalidArgumentException If the prefix is not a string.
     * @return CacheConfig Chainable
     */
    public function setPrefix($prefix)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException(
                'Prefix must be a string'
            );
        }

        /** @see \Stash\Pool\::setNamespace */
        if (!ctype_alnum($prefix)) {
            throw new InvalidArgumentException(
                'Prefix must be alphanumeric'
            );
        }

        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Retrieve the cache namespace.
     *
     * @return string
     */
    public function prefix()
    {
        return $this->prefix;
    }
}
