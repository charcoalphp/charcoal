<?php

namespace Charcoal\Model\Service;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Metadata Configset
 *
 * Stores the metadata loader's settings, search paths, and caching service.
 */
class MetadataConfig extends AbstractConfig
{
    /**
     * Metadata search paths.
     *
     * @var array
     */
    private $paths = [];

    /**
     * The PSR-6 caching service or cache identifier(s) to use.
     *
     * @var mixed
     */
    private $cache = true;

    /**
     * Retrieve the default values.
     *
     * @param  string|null $key Optional data key to retrieve.
     * @return mixed An associative array if $key is NULL.
     *     If $key is specified, the value of that data key if it exists, NULL on failure.
     */
    public function defaults($key = null)
    {
        $data = [
            'paths' => [],
            'cache' => true,
        ];

        if ($key) {
            return isset($data[$key]) ? $data[$key] : null;
        }

        return $data;
    }

    /**
     * Add settings to configset, replacing existing settings with the same data key.
     *
     * @see    \Charcoal\Config\AbstractConfig::merge()
     * @param  array|Traversable $data The data to merge.
     * @return self
     */
    public function merge($data)
    {
        foreach ($data as $key => $val) {
            if ($key === 'paths') {
                $this->addPaths((array)$val);
            } elseif ($key === 'cache') {
                $this->setCache($val);
            } else {
                $this->offsetReplace($key, $val);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function paths()
    {
        return $this->paths;
    }

    /**
     * @param  string[] $paths One or more search paths.
     * @return self
     */
    public function setPaths(array $paths)
    {
        $this->paths = [];
        $this->addPaths($paths);
        return $this;
    }

    /**
     * @param  string[] $paths One or more search paths.
     * @return self
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
        return $this;
    }

    /**
     * @param  string $path A directory path.
     * @throws InvalidArgumentException If the path is not a string.
     * @return self
     */
    public function addPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Metadata path must be a string'
            );
        }
        $this->paths[] = $path;
        return $this;
    }

    /**
     * @return mixed
     */
    public function cache()
    {
        return isset($this->cache) ? $this->cache : false;
    }

    /**
     * @param  mixed $cache The cache service for
     *      the {@see \Charcoal\Model\Service\MetadataLoader}. If $cache is:
     *     - NULL, the {@see self::defaults() default value} will be applied.
     *     - TRUE (default), the default "cache" service will be used.
     *     - FALSE, the {@see \Charcoal\Model\Service\MetadataLoader} will use
     *       a running memory cache.
     *     - one or more {@see \Charcoal\App\Config\CacheConfig::validTypes() cache driver keys},
     *       the first cache driver available on the system will be used.
     *     - a {@see \Psr\Cache\CacheItemPoolInterface PSR-6 caching service},
     *       that instance will be used by the {@see \Charcoal\Model\Service\MetadataLoader}.
     * @throws InvalidArgumentException If the cache option is invalid.
     * @return self
     */
    public function setCache($cache)
    {
        if ($cache === null) {
            $this->cache = $this->defaults('cache');
            return $this;
        }

        if (is_bool($cache)) {
            $this->cache = $cache;
            return $this;
        }

        if (is_string($cache)) {
            $this->cache = (array)$cache;
            return $this;
        }

        if (is_array($cache)) {
            $this->cache = $cache;
            return $this;
        }

        if (is_object($cache)) {
            $this->cache = $cache;
            return $this;
        }

        throw new InvalidArgumentException(
            'Metadata cache must be a cache driver key, a PSR-6 cache pool instance, or boolean'
        );
    }
}
