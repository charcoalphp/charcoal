<?php

namespace Charcoal\Model;

// PHP dependencies
use \InvalidArgumentException;

// Module `charcoal-app` dependencies
use \Charcoal\App\App;

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
     * @return \Stash\Pool
     */
    protected function cachePool()
    {
        $container = App::instance()->getContainer();

        return $container->get('cache');
    }

    /**
     * FileLoader > searchPath(). Get the object's search path, merged with global configuration.
     *
     * This method looks in standard's `parent::searchPath()` but adds all the path defined in the
     * `metadataPath` global configuration.
     *
     * @return array
     */
    public function searchPath()
    {
        $cfg = App::instance()->getContainer()->get('config');

        $allPath = parent::searchPath();

        $globalPath = $cfg->get('metadata_path');
        if (!empty($globalPath)) {
            $allPath = array_merge($globalPath, $allPath);
        }
        return $allPath;
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

        $cachePool = $this->cachePool();
        if ($cachePool) {
            $cacheItem = $cachePool->getItem('metadata', $this->ident());

            $metadata = $cacheItem->get();
            if ($cacheItem->isMiss()) {
                $cacheItem->lock();

                $metadata = $this->loadData($ident);

                $cacheItem->set($metadata);
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
        $filename = $this->filenameFromIdent($ident);

        $file_content = $this->loadFirstFromSearchPath($filename);
        if ($file_content === null) {
            return null;
        }

        // Decode as an array (2nd parameter, true = array)
        $fileData = json_decode($file_content, true);
        $errCode = json_last_error();
        if ($errCode == JSON_ERROR_NONE) {
            return $fileData;
        }

        // Handle JSON error
        switch ($errCode) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                $errMsg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $errMsg = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $errMsg = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $errMsg = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $errMsg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $errMsg = 'Unknown error';
                break;
        }

        throw new InvalidArgumentException(
            sprintf('JSON %s could not be parsed: "%s"', $ident, $errMsg)
        );
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
    * @param array $array1 Initial array to merge.
    * @param array $... Variable list of arrays to merge.
    * @throws InvalidArgumentException If there isn't at least 2 arguments or any arguments are not an array
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
