<?php

namespace Charcoal\View;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Charcoal;
use \Charcoal\Loader\FileLoader;

// Local namespace dependencies
use \Charcoal\View\AbstractView;

/**
*
*/
class ViewTemplateLoader extends FileLoader
{
    /**
    * @var string $engine
    */
    private $_engine = 'mustache';// = AbstractView::DEFAULT_ENGINE;

    /**
    * @param string $engine
    * @throws InvalidArgumentException
    * @return ViewTemplateLoader
    */
    public function set_engine($engine)
    {
        if (!is_string($engine)) {
            throw new InvalidArgumentException('Engine must be a string.');
        }
        $this->_engine = $engine;
        return $this;
    }

    /**
    * @return string
    */
    public function engine()
    {
        return $this->_engine;
    }

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
    * @param string $ident
    * @return string
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

        $engine = $this->engine();

        $data = '';
        $filename = $this->_filename_from_ident($ident);
        $search_path = $this->search_path();
        foreach ($search_path as $path) {
            $f = $path.'/'.$filename;
            if (!file_exists($f)) {
                continue;
            }

            if ($engine == AbstractView::ENGINE_MUSTACHE) {
                $file_content = file_get_contents($f);
            } else {
                ob_start();
                include $f;
                $file_content = ob_get_clean();

            }
            if ($file_content !== '') {
                $data = $file_content;
                break;
            }
        }
        $this->set_content($data);
        $this->cache_store();

        return $data;
    }

    /**
    * Generate a sanitized filename (with extension) from an ident string
    *
    * @param string $ident
    * @return string
    */
    private function _filename_from_ident($ident)
    {
        $engine = $this->engine();
        $filename = str_replace(['\\'], '.', $ident);
        if ($engine == AbstractView::ENGINE_MUSTACHE) {
            $filename .= '.mustache';
        } else {
            $filename .= '.php';
        }

        return $filename;
    }
}
