<?php
/**
*
*/

namespace Charcoal\Metadata;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Loader\FileLoader as FileLoader;

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

        // Attempt loading from cache
        $ret = $this->cache_load();
        if ($ret !== false) {
            return $ret;
        }

        $hierarchy = $this->_hierarchy();

        $metadata = [];
        foreach ($hierarchy as $id) {
            $ident_data = self::_load_ident($id);
            if (is_array($ident_data)) {
                $metadata = Charcoal::merge($metadata, $ident_data);
            }
        }

        $this->set_content($metadata);
        $this->cache_store();

        return $metadata;
    }

    /**
    * @return array
    */
    private function _hierarchy()
    {
        $ident = $this->ident();
        $hierarchy = null;

        $classname = $this->_ident_to_classname($ident);
        //var_dump($classname);
        if (class_exists($classname)) {
            // If the object is a class, we use hierarchy from object ancestor classes
            $ident_hierarchy = [$ident];
            while ($classname = get_parent_class($classname)) {
                $ident_hierarchy[] = $this->_classname_to_ident($classname);
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
    * @param string $ident
    * @return array
    */
    private function _load_ident($ident)
    {
        $data = [];
        $filename = $this->_filename_from_ident($ident);
        $files = $this->_all_matching_filenames($filename);
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
    * @param string $ident
    * @return string
    */
    private function _filename_from_ident($ident)
    {
        $filename = str_replace(['\\'], '.', $ident);
        $filename .= '.json';

        return $filename;

    }

    /**
    * @param string $ident
    * @return string
    */
    protected function _ident_to_classname($ident)
    {
        $class = str_replace('/', '\\', $ident);
        $expl = explode('\\', $class);
        array_walk(
            $expl, function(&$i) {
                $i = ucfirst($i);
            }
        );
        $class = '\\'.implode('\\', $expl);
        return $class;
    }

    /**
    * @param string $classname
    * @return string
    */
    protected function _classname_to_ident($classname)
    {
        $ident = str_replace('\\', '/', strtolower($classname));
        $ident = ltrim($ident, '/');
        return $ident;
    }
}
