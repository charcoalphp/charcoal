<?php

namespace Charcoal\Loader;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

/**
*
*/
class FileLoader
{
    /**
    * @var array $searchPath
    */
    private $searchPath = [];

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
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                __CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.'
            );
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
    public function setPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('setPath() expects a string.');
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

        $filename = $this->firstMatchingFilename($ident);
        if ($filename) {
            $file_content = file_get_contents($filename);
            $this->set_content($file_content);
            return $file_content;
        }

        return '';
    }

    /**
    * @param string $filename
    * @return string|null The file content, or null if no file found.
    */
    protected function loadFirstFromSearchPath($filename)
    {
        $searchPath = $this->searchPath();
        if (empty($searchPath)) {
            return null;
        }
        foreach ($searchPath as $path) {
            $f = $path.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($f)) {
                $fileContent = file_get_contents($f);
                return $fileContent;
            }
        }

        return null;
    }

    /**
    * @param string $filename
    * @return string
    */
    protected function firstMatchingFilename($filename)
    {
        if (file_exists($filename)) {
            return $filename;
        }
        $searchPath = $this->searchPath();
        if (empty($searchPath)) {
            return null;
        }
        foreach ($searchPath as $path) {
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
    protected function allMatchingFilenames($filename)
    {
        $ret = [];
        if (file_exists($filename)) {
            $ret[] = $filename;
        }

        $searchPath = $this->searchPath();
        if (empty($searchPath)) {
            return $ret;
        }
        foreach ($searchPath as $path) {
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
    public function addPath($path)
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

        $this->searchPath[] = $path;

        return $this;
    }

    /**
    * Get the object's search path, merged with global configuration path
    * @return array
    */
    public function searchPath()
    {
        return $this->searchPath;
    }
}
