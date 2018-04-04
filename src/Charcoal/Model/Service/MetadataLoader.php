<?php

namespace Charcoal\Model\Service;

use RuntimeException;
use InvalidArgumentException;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From PSR-6
use Psr\Cache\CacheItemPoolInterface;

// From 'charcoal-core'
use Charcoal\Model\MetadataInterface;

/**
 * Load metadata from JSON file(s).
 *
 * The Metadata Loader is different than the `FileLoader` class it extends mainly because
 * it tries to find all files matching  the "ident" in all search path and merge them together
 * in an array, to be filled in a `Metadata` object.
 *
 * If `ident` is an actual class name, then it will also try to load all the JSON matching
 * the class' parents and interfaces.
 */
final class MetadataLoader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The PSR-6 caching service.
     *
     * @var CacheItemPoolInterface $cachePool
     */
    private $cachePool;

    /**
     * The cache of class/interface lineages.
     *
     * @var array
     */
    private static $lineageCache = [];

    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    private static $snakeCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    private static $camelCache = [];

    /**
     * The base path to prepend to any relative paths to search in.
     *
     * @var string
     */
    private $basePath = '';

    /**
     * The paths to search in.
     *
     * @var array
     */
    private $paths = [];

    /**
     * Return new MetadataLoader object.
     *
     * The application's metadata paths, if any, are merged with
     * the loader's search paths.
     *
     * # Required dependencie
     * - `logger`
     * - `cache`
     * - `paths`
     * - `base_path`
     *
     * @param  array $data The loader's dependencies.
     * @return void
     */
    public function __construct(array $data = null)
    {
        $this->setLogger($data['logger']);
        $this->setCachePool($data['cache']);
        $this->setBasePath($data['base_path']);
        $this->setPaths($data['paths']);
    }

    /**
     * Load the metadata for the given identifier or interfaces.
     *
     * @param  string            $ident      The metadata identifier to load or
     *     to use as the cache key if $interfaces is provided.
     * @param  MetadataInterface $metadata   The metadata object to load into.
     * @param  array|null        $interfaces One or more metadata identifiers to load.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return MetadataInterface Returns the cached metadata instance or if it's stale or empty,
     *     loads a fresh copy of the data into $metadata and returns it;
     */
    public function load($ident, MetadataInterface $metadata, array $interfaces = null)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Metadata identifier must be a string'
            );
        }

        if (strpos($ident, '\\') !== false) {
            $ident = $this->classnameToIdent($ident);
        }

        if (is_array($interfaces) && empty($interfaces)) {
            $interfaces = null;
        }

        $cacheKey  = $this->cacheKeyFromMetaKey($ident);
        $cacheItem = $this->cachePool()->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $data = $cacheItem->get();

            if ($data instanceof MetadataInterface) {
                $data = $data->data();
                $cacheItem->set($data);
                $this->cachePool()->save($cacheItem);
            }

            $metadata->setData($data);
        } else {
            if ($interfaces === null) {
                $data = $this->loadData($ident);
            } else {
                $data = $this->loadDataArray($interfaces);
            }

            $metadata->setData($data);
            $cacheItem->set($data);
            $this->cachePool()->save($cacheItem);
        }

        return $metadata;
    }

    /**
     * Fetch the metadata for the given identifier.
     *
     * @param  string $ident The metadata identifier to load.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return array
     */
    public function loadData($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Metadata identifier must be a string'
            );
        }

        $lineage = $this->hierarchy($ident);
        $catalog = [];
        foreach ($lineage as $id) {
            $data = $this->loadFileFromIdent($id);

            if (is_array($data)) {
                $catalog = array_replace_recursive($catalog, $data);
            }
        }

        return $catalog;
    }

    /**
     * Fetch the metadata for the given identifiers.
     *
     * @param  array $idents One or more metadata identifiers to load.
     * @return array
     */
    public function loadDataArray(array $idents)
    {
        $catalog = [];
        foreach ($idents as $id) {
            $data = $this->loadData($id);

            if (is_array($data)) {
                $catalog = array_replace_recursive($catalog, $data);
            }
        }

        return $catalog;
    }

    /**
     * Build a class/interface lineage from the given snake-cased namespace.
     *
     * @param  string $ident The FQCN (in snake-case) to load the hierarchy from.
     * @return array
     */
    private function hierarchy($ident)
    {
        if (!is_string($ident)) {
            return [];
        }

        if (isset(static::$lineageCache[$ident])) {
            return static::$lineageCache[$ident];
        }

        $classname = $this->classNameFromMetaKey($ident);

        return $this->classLineage($classname, $ident);
    }

    /**
     * Build a class/interface lineage from the given PHP namespace.
     *
     * @param  string      $classname The FQCN to load the hierarchy from.
     * @param  string|null $ident     Optional. The snake-cased $classname.
     * @return array
     */
    private function classLineage($classname, $ident = null)
    {
        if (!is_string($classname)) {
            return [];
        }

        if ($ident === null) {
            $ident = $this->metaKeyFromClassName($classname);
        }

        if (isset(static::$lineageCache[$ident])) {
            return static::$lineageCache[$ident];
        }

        $classname = $this->classNameFromMetaKey($ident);

        if (!class_exists($classname) && !interface_exists($classname)) {
            return [ $ident ];
        }

        $classes   = array_values(class_parents($classname));
        $classes   = array_reverse($classes);
        $classes[] = $classname;

        $hierarchy = [];
        foreach ($classes as $class) {
            $implements = array_values(class_implements($class));
            $implements = array_reverse($implements);
            foreach ($implements as $interface) {
                $hierarchy[$this->metaKeyFromClassName($interface)] = 1;
            }
            $hierarchy[$this->metaKeyFromClassName($class)] = 1;
        }

        $hierarchy = array_keys($hierarchy);

        static::$lineageCache[$ident] = $hierarchy;

        return $hierarchy;
    }

    /**
     * Load a metadata file from the given metdata identifier.
     *
     * The file is converted to JSON, the only supported format.
     *
     * @param  string $ident The metadata identifier to fetch.
     * @return array|null
     */
    private function loadFileFromIdent($ident)
    {
        $filename = $this->filePathFromMetaKey($ident);

        return $this->loadFile($filename);
    }

    /**
     * Load a file as an array.
     *
     * Supported file types: JSON.
     *
     * @param  string $filename A file path to resolve and fetch.
     * @return array|null An associative array on success, NULL on failure.
     */
    private function loadFile($filename)
    {
        if (file_exists($filename)) {
            return $this->loadJsonFile($filename);
        }

        $paths = $this->paths();

        if (empty($paths)) {
            return null;
        }

        $ret = [];
        $paths = array_reverse($paths);
        foreach ($paths as $basePath) {
            $file = $basePath.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($file)) {
                $ret = array_replace_recursive($ret, $this->loadJsonFile($file));
            }
        }
        if (!empty($ret)) {
            return $ret;
        } else {
            return null;
        }
    }

    /**
     * Load a JSON file as an array.
     *
     * @param  string $path A path to a JSON file.
     * @throws UnexpectedValueException If the file can not correctly be parsed into an array.
     * @return array An associative array on success.
     */
    private function loadJsonFile($path)
    {
        $data = json_decode(file_get_contents($path), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg() ?: 'Unknown error';
            throw new UnexpectedValueException(
                sprintf('JSON file "%s" could not be parsed: "%s"', $path, $error)
            );
        }

        if (!is_array($data)) {
            throw new UnexpectedValueException(
                sprintf('JSON file "%s" does not return an array', $path)
            );
        }

        return $data;
    }

    /**
     * Generate a cache key.
     *
     * @param  string $ident The metadata identifier to convert.
     * @return string
     */
    public function cacheKeyFromMetaKey($ident)
    {
        $cacheKey = 'metadata/'.str_replace('/', '.', $ident);
        return $cacheKey;
    }

    /**
     * Convert a snake-cased namespace to a file path.
     *
     * @param  string $ident The metadata identifier to convert.
     * @return string
     */
    private function filePathFromMetaKey($ident)
    {
        $filename  = str_replace('\\', '.', $ident);
        $filename .= '.json';

        return $filename;
    }

    /**
     * Convert a kebab-cased namespace to CamelCase.
     *
     * @param  string $ident The metadata identifier to convert.
     * @return string Returns a valid PHP namespace.
     */
    private function classNameFromMetaKey($ident)
    {
        $key = $ident;

        if (isset(static::$camelCache[$key])) {
            return static::$camelCache[$key];
        }

        // Change "foo-bar" to "fooBar"
        $parts = explode('-', $ident);
        array_walk(
            $parts,
            function(&$i) {
                $i = ucfirst($i);
            }
        );
        $ident = implode('', $parts);

        // Change "/foo/bar" to "\Foo\Bar"
        $classname = str_replace('/', '\\', $ident);
        $parts     = explode('\\', $classname);

        array_walk(
            $parts,
            function(&$i) {
                $i = ucfirst($i);
            }
        );

        $classname = trim(implode('\\', $parts), '\\');

        static::$camelCache[$key]       = $classname;
        static::$snakeCache[$classname] = $key;

        return $classname;
    }

    /**
     * Convert a CamelCase namespace to kebab-case.
     *
     * @param  string $class The FQCN to convert.
     * @return string Returns a kebab-cased namespace.
     */
    private function metaKeyFromClassName($class)
    {
        $key = trim($class, '\\');

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        $ident = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $class));
        $ident = str_replace('\\', '/', strtolower($ident));
        $ident = ltrim($ident, '/');

        static::$snakeCache[$key]   = $ident;
        static::$camelCache[$ident] = $key;

        return $ident;
    }

    /**
     * Assign a base path for relative search paths.
     *
     * @param  string $basePath The base path to use.
     * @throws InvalidArgumentException If the base path parameter is not a string.
     * @return void
     */
    private function setBasePath($basePath)
    {
        if (!is_string($basePath)) {
            throw new InvalidArgumentException(
                'Base path must be a string'
            );
        }

        $basePath = realpath($basePath);
        $this->basePath = rtrim($basePath, '/\\').DIRECTORY_SEPARATOR;
    }

    /**
     * Retrieve the base path for relative search paths.
     *
     * @return string
     */
    private function basePath()
    {
        return $this->basePath;
    }

    /**
     * Assign many search paths.
     *
     * @param  string[] $paths One or more search paths.
     * @return void
     */
    private function setPaths(array $paths)
    {
        $this->paths = [];
        $this->addPaths($paths);
    }

    /**
     * Retrieve search paths.
     *
     * @return string[]
     */
    private function paths()
    {
        return $this->paths;
    }

    /**
     * Append many search paths.
     *
     * @param  string[] $paths One or more search paths.
     * @return self
     */
    private function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }

        return $this;
    }

    /**
     * Append a search path.
     *
     * @param  string $path A file or directory path.
     * @return self
     */
    private function addPath($path)
    {
        $path = $this->resolvePath($path);

        if ($this->validatePath($path)) {
            $this->paths[] = $path;
        }

        return $this;
    }

    /**
     * Parse a relative path using the base path if needed.
     *
     * @param  string $path The path to resolve.
     * @throws InvalidArgumentException If the path is invalid.
     * @return string
     */
    private function resolvePath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path needs to be a string'
            );
        }

        $basePath = $this->basePath();
        $path = ltrim($path, '/\\');

        if ($basePath && strpos($path, $basePath) === false) {
            $path = $basePath.$path;
        }

        return $path;
    }

    /**
     * Validate a resolved path.
     *
     * @param  string $path The path to validate.
     * @return string
     */
    private function validatePath($path)
    {
        return file_exists($path);
    }

    /**
     * Set the cache service.
     *
     * @param  CacheItemPoolInterface $cache A PSR-6 compliant cache pool instance.
     * @return void
     */
    private function setCachePool(CacheItemPoolInterface $cache)
    {
        $this->cachePool = $cache;
    }

    /**
     * Retrieve the cache service.
     *
     * @return CacheItemPoolInterface
     */
    private function cachePool()
    {
        return $this->cachePool;
    }
}
