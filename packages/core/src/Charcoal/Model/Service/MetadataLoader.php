<?php

namespace Charcoal\Model\Service;

use RuntimeException;
use InvalidArgumentException;
use UnexpectedValueException;
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
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * The cache of metadata instances, indexed by metadata identifier.
     *
     * @var MetadataInterface[]
     */
    private static $metadataCache = [];

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
     * Notes:
     * - If the requested dataset is found, it will be stored in the cache service.
     * - If the provided metadata container is an {@see MetadataInterface object},
     *   it will be stored for the lifetime of the script (whether it be a longer
     *   running process or a web request).
     *
     * @param  string $ident    The metadata identifier to load.
     * @param  mixed  $metadata The metadata type to load the dataset into.
     *     If $metadata is a {@see MetadataInterface} instance, the requested dataset will be merged into the object.
     *     If $metadata is a class name, the requested dataset will be stored in a new instance of that class.
     *     If $metadata is an array, the requested dataset will be merged into the array.
     * @param  array  $idents   The metadata identifier(s) to load.
     *     If $idents is provided, $ident will be used as the cache key
     *     and $idents are loaded instead.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return MetadataInterface|array Returns the dataset, for the given $ident,
     *     as an array or an instance of {@see MetadataInterface}.
     *     See $metadata for more details.
     */
    public function load($ident, $metadata = [], array $idents = null)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(sprintf(
                'Metadata identifier must be a string, received %s',
                is_object($ident) ? get_class($ident) : gettype($ident)
            ));
        }

        if (strpos($ident, '\\') !== false) {
            $ident = $this->metaKeyFromClassName($ident);
        }

        $valid = $this->validateMetadataContainer($metadata, $metadataType, $targetMetadata);
        if ($valid === false) {
            throw new InvalidArgumentException(sprintf(
                'Metadata object must be a class name or instance of %s, received %s',
                MetadataInterface::class,
                is_object($metadata) ? get_class($metadata) : gettype($metadata)
            ));
        }

        if (isset(static::$metadataCache[$ident])) {
            $cachedMetadata = static::$metadataCache[$ident];

            if (is_object($targetMetadata)) {
                return $targetMetadata->merge($cachedMetadata);
            } elseif (is_array($targetMetadata)) {
                return array_replace_recursive($targetMetadata, $cachedMetadata->data());
            }

            return $cachedMetadata;
        }

        $data = $this->loadMetadataFromCache($ident, $idents);

        if (is_object($targetMetadata)) {
            return $targetMetadata->merge($data);
        } elseif (is_array($targetMetadata)) {
            return array_replace_recursive($targetMetadata, $data);
        }

        $targetMetadata = new $metadataType();
        $targetMetadata->setData($data);

        static::$metadataCache[$ident] = $targetMetadata;

        return $targetMetadata;
    }

    /**
     * Fetch the metadata for the given identifier.
     *
     * @param  string $ident The metadata identifier to load.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return array
     */
    public function loadMetadataByKey($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Metadata identifier must be a string'
            );
        }

        $lineage  = $this->hierarchy($ident);
        $metadata = [];
        foreach ($lineage as $metaKey) {
            $data = $this->loadMetadataFromSource($metaKey);
            if (is_array($data)) {
                $metadata = array_replace_recursive($metadata, $data);
            }
        }

        return $metadata;
    }

    /**
     * Fetch the metadata for the given identifiers.
     *
     * @param  array $idents One or more metadata identifiers to load.
     * @return array
     */
    public function loadMetadataByKeys(array $idents)
    {
        $metadata = [];
        foreach ($idents as $metaKey) {
            $data = $this->loadMetadataByKey($metaKey);
            if (is_array($data)) {
                $metadata = array_replace_recursive($metadata, $data);
            }
        }

        return $metadata;
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
     * @param  string      $class The FQCN to load the hierarchy from.
     * @param  string|null $ident Optional. The snake-cased $class.
     * @return array
     */
    private function classLineage($class, $ident = null)
    {
        if (!is_string($class)) {
            return [];
        }

        if ($ident === null) {
            $ident = $this->metaKeyFromClassName($class);
        }

        if (isset(static::$lineageCache[$ident])) {
            return static::$lineageCache[$ident];
        }

        $class = $this->classNameFromMetaKey($ident);

        if (!class_exists($class) && !interface_exists($class)) {
            return [ $ident ];
        }

        $classes   = array_values(class_parents($class));
        $classes   = array_reverse($classes);
        $classes[] = $class;

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
     * Load a metadataset from the cache.
     *
     * @param  string $ident  The metadata identifier to load / cache key for $idents.
     * @param  array  $idents If provided, $ident is used as the cache key
     *     and these metadata identifiers are loaded instead.
     * @return array The data associated with the metadata identifier.
     */
    private function loadMetadataFromCache($ident, array $idents = null)
    {
        $cacheKey  = $this->cacheKeyFromMetaKey($ident);
        $cacheItem = $this->cachePool()->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $metadata = $cacheItem->get();

            /** Backwards compatibility */
            if ($metadata instanceof MetadataInterface) {
                $metadata = $metadata->data();
                $cacheItem->set($metadata);
                $this->cachePool()->save($cacheItem);
            }

            return $metadata;
        } else {
            if (empty($idents)) {
                $metadata = $this->loadMetadataByKey($ident);
            } else {
                $metadata = $this->loadMetadataByKeys($idents);
            }

            $cacheItem->set($metadata);
            $this->cachePool()->save($cacheItem);
        }

        return $metadata;
    }

    /**
     * Load a metadata file from the given metdata identifier.
     *
     * The file is converted to JSON, the only supported format.
     *
     * @param  string $ident The metadata identifier to fetch.
     * @return array|null An associative array on success, NULL on failure.
     */
    private function loadMetadataFromSource($ident)
    {
        $path = $this->filePathFromMetaKey($ident);
        return $this->loadFile($path);
    }

    /**
     * Load a file as an array.
     *
     * Supported file types: JSON.
     *
     * @param  string $path A file path to resolve and fetch.
     * @return array|null An associative array on success, NULL on failure.
     */
    private function loadFile($path)
    {
        if (file_exists($path)) {
            return $this->loadJsonFile($path);
        }

        $dirs = $this->paths();
        if (empty($dirs)) {
            return null;
        }

        $data = [];
        $dirs = array_reverse($dirs);
        foreach ($dirs as $dir) {
            $file = $dir . DIRECTORY_SEPARATOR . $path;
            if (file_exists($file)) {
                $data = array_replace_recursive($data, $this->loadJsonFile($file));
            }
        }

        if (empty($data)) {
            return null;
        }

        return $data;
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
     * Generate a store key.
     *
     * @param  string|string[] $ident The metadata identifier(s) to convert.
     * @return string
     */
    public function serializeMetaKey($ident)
    {
        if (is_array($ident)) {
            sort($ident);
            $ident = implode(':', $ident);
        }

        return md5($ident);
    }

    /**
     * Generate a cache key.
     *
     * @param  string $ident The metadata identifier to convert.
     * @return string
     */
    public function cacheKeyFromMetaKey($ident)
    {
        $cacheKey = 'metadata/' . str_replace('/', '.', $ident);
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
            function (&$i) {
                $i = ucfirst($i);
            }
        );
        $ident = implode('', $parts);

        // Change "/foo/bar" to "\Foo\Bar"
        $classname = str_replace('/', '\\', $ident);
        $parts     = explode('\\', $classname);

        array_walk(
            $parts,
            function (&$i) {
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
     * Validate a metadata type or container.
     *
     * If specified, the method will also resolve the metadata type or container.
     *
     * @param  mixed       $metadata The metadata type or container to validate.
     * @param  string|null $type     If provided, then it is filled with the resolved metadata type.
     * @param  mixed|null  $bag      If provided, then it is filled with the resolved metadata container.
     * @return boolean
     */
    private function validateMetadataContainer($metadata, &$type = null, &$bag = null)
    {
        // If variables are provided, clear existing values.
        $type = null;
        $bag  = null;

        if (is_array($metadata)) {
            $type = 'array';
            $bag  = $metadata;
            return true;
        }

        if (is_a($metadata, MetadataInterface::class, true)) {
            if (is_object($metadata)) {
                $type = get_class($metadata);
                $bag  = $metadata;
                return true;
            }
            if (is_string($metadata)) {
                $type = $metadata;
                return true;
            }
        }

        return false;
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
        $this->basePath = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR;
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
     * @param  string $path A directory path.
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
        $path = trim($path, '/\\');

        if ($basePath && strpos($path, $basePath) === false) {
            $path = $basePath . $path;
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
        return is_dir($path);
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
