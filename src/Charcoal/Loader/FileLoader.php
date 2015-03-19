<?php

namespace Charcoal\Loader;

use \Charcoal\Charcoal as Charcoal;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;

class FileLoader extends Loader
{

	private $_search_path = [];

	private $_filesystem;

	private $_path;


	public function set_path($path)
	{
		if(!is_string($path)) {
			throw new \InvalidArgumentException('set_path() expects a string');
		}
		$this->_path = $path;
		return $this;
	}

	public function path()
	{
		if(!$this->_path) {
			return '';
		}
		return $this->_path;
	}

	/**
	* @
	*/
	public function filesystem()
	{
		if($this->_filesystem === null) {
			$adapter = new LocalAdapter($this->path());
			$this->_filesystem = new Filesystem($adapter);
		}
	}

	/**
	* Returns the content of the first file found in search path
	*
	* @return string File content
	*/
	public function load($filename=null)
	{
		if($filename === null) {
			return '';
		}

		// Attempt loading from cache
		$ret = $this->_load_from_cache();
		if($ret !== false) {
			return $ret;
		}

		$filename = $this->_first_matching_filename($filename);
		if($filename) {
			$file_content = file_get_contents($filename);
			$this->_cache($file_content);
			return $file_content;
		}

		return '';
	}

	protected function _load_first_from_search_path($filename)
	{
		$search_path = $this->search_path();
		if(empty($search_path)) {
			return '';
		}
		foreach($search_path as $path) {
			$f = $path.DIRECTORY_SEPARATOR.$filename;
			if(file_exists($f)) {
				$file_content = file_get_contents($f);
				return $file_content;
			}	
		}

		return '';
	}

	protected function _first_matching_filename($filename)
	{
		if(file_exists($filename)) {
			return $filename;
		}
		$search_path = $this->search_path();
		if(empty($search_path)) {
			return null;
		}
		foreach($search_path as $path) {
			$f = $path.DIRECTORY_SEPARATOR.$filename;
			if(file_exists($f)) {
				return $f;
			}
		}

		return null;
	}

	/**
	* @return array
	*/
	protected function _all_matching_filenames($filename)
	{
		$ret = [];
		if(file_exists($filename)) {
			$ret[] = $filename;
		}

		$search_path = $this->search_path();
		if(empty($search_path)) {
			return $ret;
		}
		foreach($search_path as $path) {
			$f = $path.DIRECTORY_SEPARATOR.$filename;
			if(file_exists($f)) {
				$ret[] = $f;
			}
		}

		return $ret;
	}

	/**
	* @param string $path
	*
	* @throws \InvalidArgumentException if the path does not exist or is invalid
	* @return \Charcoal\Service\Loader\Metadata (Chainable)
	*/
	public function add_path($path)
	{
		if(!is_string($path)) {
			throw new \InvalidArgumentException('Path should be a string.');
		}
		if(!file_exists($path)) {
			throw new \InvalidArgumentException(sprintf('Path does not exist: %s', $path));
		}
		if(!is_dir($path)) {
			throw new \InvalidArgumentException(sprintf('Path is not a directory: %s', $path));
		}

		$this->_search_path[] = $path;

		return $this;
	}

	/**
	* Get the object's search path, merged with global configuration path
	* @return array
	*/
	public function search_path()
	{
		return $this->_search_path;
	}
}