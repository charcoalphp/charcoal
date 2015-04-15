<?php

namespace Charcoal\View;


use \Mustache_loader as Mustache_Loader;

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
        if(!empty($global_path)) {
            $all_path = array_merge($global_path, $all_path);
        }
        return $all_path;
    }

    /**
    * AbstractLoader > load()
    *
    * @return string
    */
    public function load($ident=null)
    {
        if($ident !== null) {
            $this->set_ident($ident);
        }

        // Attempt loading from cache
        $ret = $this->cache_load();
        if($ret !== false) {
            return $ret;
        }

        $data = '';
        $filename = $this->_filename_from_ident($ident);

        $search_path = $this->search_path();
        foreach($search_path as $path) {
            $f = $path.'/'.$filename;
            if(!file_exists($f)) {
                continue;
            }
            $file_content = file_get_contents($f);
            if($file_content !== '') {
                $data = $file_content;
                break;
            }
        }
        $this->set_content($data);
        $this->cache_store();

        return $data;
    }

    /**
    * @param string
    * @return string
    */
    private function _filename_from_ident($ident)
    {
        $filename = str_replace(['\\'], '.', $ident);
        $filename .= '.php';

        return $filename;
    }
}
