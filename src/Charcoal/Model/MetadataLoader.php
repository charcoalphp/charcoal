<?php

namespace Charcoal\Model;

// PHP dependencies
use \InvalidArgumentException;

// Dependencies from `PSR-6`
use \Psr\Cache\CacheItemPoolInterface;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Loader\FileLoader;

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
class MetadataLoader extends FileLoader
{
    /**
     * @var CacheItemPoolInterface $cachePool
     */
    private $cachePool;

    /**
     * Return new MetadataLoader object.
     *
     * The application's metadata paths, if any, are merged with
     * the loader's search paths.
     *
     * # Required dependencie
     * - `config`
     * - `cache`
     *
     * # Optional dependencies
     * - `paths`
     * - `base_path`
     *
     * @param array $data The loader's dependencies.
     */
    public function __construct(array $data = null)
    {
        $config = $data['config'];

        $basePath = $config['base_path'];
        $metadataPaths = $config['metadata.paths'];

        if (isset($metadataPaths)) {
            if (isset($data['paths'])) {
                $data['paths'] = array_merge($metadataPaths, $data['paths']);
            } else {
                $data['paths'] = $metadataPaths;
            }
        }

        if (!isset($data['base_path']) && $basePath) {
            $data['base_path'] = $basePath;
        }

        $this->setCachePool($data['cache']);

        parent::__construct($data);
    }

    /**
     * @param CacheItemPoolInterface $cache A PSR-6 compliant cache pool instance.
     * @return MetadataLoader Chainable
     */
    public function setCachePool(CacheItemPoolInterface $cache)
    {
        $this->cachePool = $cache;
        return $this;
    }

    /**
     * Retrieve the cache service provider.
     *
     * @return CacheItemPoolInterface
     */
    protected function cachePool()
    {
        return $this->cachePool;
    }

    /**
     * Validate a resolved path.
     *
     * @param  string $path The path to validate.
     * @return string
     */
    public function validatePath($path)
    {
        return (parent::validatePath($path) && is_dir($path));
    }

    /**
     * Load the metadata from JSON files.
     *
     * @param string $ident Optional, set the ident to load.
     * @return array
     */
    public function load($ident = null)
    {
        if ($ident !== null) {
            $this->setIdent($ident);
        }

        $ident = $this->ident();

        $cachePool = $this->cachePool();
        if ($cachePool) {
            $cacheItem = $cachePool->getItem('metadata/'.$ident);

            $metadata = $cacheItem->get();
            if ($cacheItem->isMiss()) {
                $cacheItem->lock();

                $metadata = $this->loadData($ident);

                $cachePool->save($cacheItem->set($metadata));
            }
        } else {
            $metadata = $this->loadData($ident);
        }

        return $metadata;
    }

    /**
     * Load the metadata from JSON files.
     *
     * @param string $ident Optional, set the ident to load.
     * @return array
     */
    public function loadData($ident = null)
    {
        if ($ident !== null) {
            $this->setIdent($ident);
        }

        $hierarchy = $this->hierarchy();

        $metadata = [];
        foreach ($hierarchy as $id) {
            $identData = self::loadIdent($id);

            if (is_array($identData)) {
                $metadata = array_replace_recursive($metadata, $identData);
            }
        }

        return $metadata;
    }

    /**
     * @return array
     */
    private function hierarchy()
    {
        $ident = $this->ident();
        $hierarchy = null;

        $classname = $this->identToClassname($ident);

        if (class_exists($classname)) {
            // If the object is a class, we use hierarchy from object ancestor classes
            $ident_hierarchy = [$ident];

            // Get interfaces
            // class_implements returns parent classes interfaces at first
            $implements = array_values(class_implements($classname));

            foreach ($implements as $interface) {
                $ident_hierarchy[] = $this->classnameToIdent($interface);
            }

            while ($classname = get_parent_class($classname)) {
                $ident_hierarchy[] = $this->classnameToIdent($classname);
            }

            $ident_hierarchy = array_reverse($ident_hierarchy);
        } else {
            if (is_array($hierarchy) && !empty($hierarchy)) {
                $hierarchy[] = $ident;
                $ident_hierarchy = $hierarchy;
            } else {
                $ident_hierarchy = [$ident];
            }
        }
        return $ident_hierarchy;
    }

    /**
     * Get an "ident" (file) from all search path and merge the content
     *
     * @param string $ident The identifier from which to retrieve a file.
     * @throws InvalidArgumentException If a JSON decoding error occurs.
     * @return array|null
     */
    private function loadIdent($ident)
    {
        $name = $this->filenameFromIdent($ident);
        $file = $this->firstMatchingFilename($name);

        if ($file) {
            return $this->loadJsonFile($file);
        }

        return null;
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param string $ident The identifier to convert.
     * @return string
     */
    private function filenameFromIdent($ident)
    {
        $filename  = str_replace([ '\\' ], '.', $ident);
        $filename .= '.json';

        return $filename;
    }

    /**
     * Convert an identifier to a FQN.
     *
     * @param string $ident The identifier to convert.
     * @return string
     */
    protected function identToClassname($ident)
    {
        // Change "foo-bar" to "fooBar"
        $expl = explode('-', $ident);
        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );
        $ident = implode('', $expl);

        // Change "/foo/bar" to "\Foo\Bar"
        $class = str_replace('/', '\\', $ident);
        $expl  = explode('\\', $class);

        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );

        $class = '\\'.trim(implode('\\', $expl), '\\');
        return $class;
    }

    /**
     * Convert a FQN to an identifier.
     *
     * @param string $classname The FQN to convert.
     * @return string
     */
    protected function classnameToIdent($classname)
    {
        $ident = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $classname));
        $ident = str_replace('\\', '/', strtolower($ident));
        $ident = ltrim($ident, '/');
        return $ident;
    }

    /**
    * Rewrite the "array_merge_recursive" function to behave more like standard "array_merge"
    * (overwrite values instead of appending them)
    *
    * From http:// www.php.net/manual/en/function.array-merge-recursive.php#104145
    *
    * @throws InvalidArgumentException If there isn't at least 2 arguments or any arguments are not an array.
    * @return array Merged array
    */
    private static function arrayMerge()
    {
        $args = func_get_args();
        if (func_num_args() < 2) {
            throw new InvalidArgumentException(
                'This function takes at least two parameters.'
            );
        }

        $array_list = func_get_args();
        $result = [];

        while ($array_list) {
            $current = array_shift($array_list);

            /** @todo Convert objects to array? */
            if (!is_array($current)) {
                throw new InvalidArgumentException(
                    'All parameters must be arrays.'
                );
            }
            if (!$current) {
                continue;
            }

            foreach ($current as $key => $value) {
                if (is_string($key)) {
                    if (is_array($value) && array_key_exists($key, $result) && is_array($result[$key])) {
                        $result[$key] = call_user_func([__CLASS__, __FUNCTION__], $result[$key], $value);
                    } else {
                        $result[$key] = $value;
                    }
                } else {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }
}
