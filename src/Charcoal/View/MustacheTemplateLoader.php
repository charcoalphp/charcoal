<?php

namespace Charcoal\View;

// 3rd-party libraries dependencies
use \Mustache_Loader as Mustache_Loader;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Loader\FileLoader as FileLoader;

/**
* Mustache Template Loader
*/
class MustacheTemplateLoader extends FileLoader implements Mustache_Loader
{
    /**
    * FileLoader > search_path()
    *
    * @return array
    */
    public function search_path()
    {
        $all_path = parent::search_path();

        $global_path = Charcoal::config()->template_path();
        if (!empty($global_path)) {
            $all_path = array_merge($global_path, $all_path);
        }
        return $all_path;
    }

    /**
    * AbstractLoader > load()
    *
    * @param string $ident
    * @return string
    */
    public function load($ident = null)
    {
        if ($ident === '$widget_template') {
            $ident = (isset($GLOBALS['widget_template']) ? $GLOBALS['widget_template'] : null);
            if ($ident === null) {
                return '';
            }
        }
        if ($ident !== null) {
            $this->set_ident($ident);
        }
        $ident = $this->ident();

        // Attempt loading from cache
        $ret = $this->cache_load();
        if ($ret !== false) {
            return $ret;
        }

        $data = '';
        $filename = $this->_filename_from_ident($ident);

        $search_path = $this->search_path();
        foreach ($search_path as $path) {
            $f = $path.'/'.$filename;
            if (!file_exists($f)) {
                continue;
            }
            $file_content = file_get_contents($f);
            if ($file_content !== '') {
                $data = $file_content;
                break;
            }
        }
        $this->set_content($data);
        $this->cache_store();
        unset($this->_content);

        return $data;
    }

    /**
    * @param string $ident
    * @return string
    */
    private function _filename_from_ident($ident)
    {
        $filename = str_replace(['\\'], '.', $ident);
        $filename .= '.mustache';

        return $filename;
    }
}
