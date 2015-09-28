<?php

namespace Charcoal\Loader;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

/**
*
*/
class FileLoader extends AbstractLoader
{
    /**
    * @var array $search_path
    */
    private $search_path = [];

    /**
    * @var string $path
    */
    private $path;

    /**
    * @var string $ident
    */
    private $ident;

    /**
    * @param string $ident
    * @throws InvalidArgumentException if the ident is not a string
    * @return FileLoader Chainable
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.');
        }
        $this->ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->ident;
    }

    /**
    * @param string $path
    * @throws InvalidArgumentException
    * @return FileLoader Chainable
    */
    public function set_path($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('set_path() expects a string.');
        }
        $this->path = $path;
        return $this;
    }

    /**
    * @return string
    */
    public function path()
    {
        if (!$this->path) {
            return '';
        }
        return $this->path;
    }

    /**
    * Returns the content of the first file found in search path
    *
    * @param string|null $ident
    * @return string File content
    */
    public function load($ident = null)
    {
        if ($ident === null) {
            return '';
        }

        // Attempt loading from cache
        $ret = $this->cache_load();
        if ($ret !== false) {
            return $ret;
        }

        $filename = $this->first_matching_filename($ident);
        if ($filename) {
            $file_content = file_get_contents($filename);
            $this->set_content($file_content);
            $this->cache_store();
            return $file_content;
        }

        return '';
    }

    /**
    * @param string $filename
    * @return string
    */
    protected function load_first_from_search_path($filename)
    {
        $search_path = $this->search_path();
        if (empty($search_path)) {
            return '';
        }
        foreach ($search_path as $path) {
            $f = $path.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($f)) {
                $file_content = file_get_contents($f);
                return $file_content;
            }
        }

        return '';
    }

    /**
    * @param string $filename
    * @return string
    */
    protected function first_matching_filename($filename)
    {
        if (file_exists($filename)) {
            return $filename;
        }
        $search_path = $this->search_path();
        if (empty($search_path)) {
            return null;
        }
        foreach ($search_path as $path) {
            $f = $path.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($f)) {
                return $f;
            }
        }

        return null;
    }

    /**
    * @param string $filename
    * @return array
    */
    protected function all_matching_filenames($filename)
    {
        $ret = [];
        if (file_exists($filename)) {
            $ret[] = $filename;
        }

        $search_path = $this->search_path();
        if (empty($search_path)) {
            return $ret;
        }
        foreach ($search_path as $path) {
            $f = $path.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($f)) {
                $ret[] = $f;
            }
        }

        return $ret;
    }

    /**
    * @param string $path
    * @throws InvalidArgumentException if the path does not exist or is invalid
    * @return \Charcoal\Service\Loader\Metadata (Chainable)
    */
    public function add_path($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path should be a string.'
            );
        }
        if (!file_exists($path)) {
            throw new InvalidArgumentException(
                sprintf('Path does not exist: "%s"', $path)
            );
        }
        if (!is_dir($path)) {
            throw new InvalidArgumentException(
                sprintf('Path is not a directory: "%s"', $path)
            );
        }

        $this->search_path[] = $path;

        return $this;
    }

    /**
    * Get the object's search path, merged with global configuration path
    * @return array
    */
    public function search_path()
    {
        return $this->search_path;
    }
}
