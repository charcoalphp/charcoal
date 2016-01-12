<?php

namespace Charcoal\Model;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Charcoal;
use \Charcoal\Loader\FileLoader;

/**
 * Load metadata from JSON file(s).
 *
 * The Metadata Loader is different than the `FileLoader` class it extends mainly because
 * it tries to find all files matching  the "ident" in all search path and merge them together
 * in an array, to be filled in a `Metadata` object.
 *
 * If `ident` is an actual class name, then it will also try to load all the JSON matching
 * the class' parents and traits.
 */
class MetadataLoader extends FileLoader
{
    /**
     * FileLoader > search_path(). Get the object's search path, merged with global configuration.
     *
     * This method looks in standard's `parent::search_path()` but adds all the path defined in the
     * `metadata_path` global configuration.
     *
     * @return array
     */
    public function search_path()
    {
        $cfg = Charcoal::config();

        $all_path = parent::search_path();

        $global_path = Charcoal::config()->metadata_path();
        if (!empty($global_path)) {
            $all_path = Charcoal::merge($global_path, $all_path);
        }
        return $all_path;
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
            $this->set_ident($ident);
        }

        $hierarchy = $this->hierarchy();

        $metadata = [];
        foreach ($hierarchy as $id) {
            $ident_data = self::load_ident($id);
            if (is_array($ident_data)) {
                $metadata = Charcoal::merge($metadata, $ident_data);
            }
        }

        $this->set_content($metadata);

        return $metadata;
    }

    /**
     * @return array
     */
    private function hierarchy()
    {
        $ident = $this->ident();
        $hierarchy = null;

        $classname = $this->ident_to_classname($ident);

        if (class_exists($classname)) {
            // If the object is a class, we use hierarchy from object ancestor classes
            $ident_hierarchy = [$ident];

            // Get interfaces
            // class_implements returns parent classes interfaces at first
            $implements = class_implements($classname);

            foreach ($implements as $interface => $val) {
                $ident_hierarchy[] = $this->classname_to_ident($interface);
            }

            while ($classname = get_parent_class($classname)) {
                $ident_hierarchy[] = $this->classname_to_ident($classname);
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
     * @return array
     */
    private function load_ident($ident)
    {
        $data = [];
        $filename = $this->filename_from_ident($ident);
        $files = $this->all_matching_filenames($filename);
        foreach ($files as $f) {
            $file_content = file_get_contents($f);
            if ($file_content === '') {
                continue;
            }
            // Decode as an array (2nd parameter, true = array)
            $file_data = json_decode($file_content, true);
            // Todo: Handle json_last_error()
            if (is_array($file_data)) {
                $data = Charcoal::merge($data, $file_data);
            }
        }
        return $data;
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param string $ident The identifier to convert.
     * @return string
     */
    private function filename_from_ident($ident)
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
    protected function ident_to_classname($ident)
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
    protected function classname_to_ident($classname)
    {
        $ident = str_replace('\\', '/', strtolower($classname));
        $ident = ltrim($ident, '/');
        return $ident;
    }
}
