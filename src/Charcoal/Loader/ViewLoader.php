<?php
/**
*
*/

namespace Charcoal\Loader;
use \Charcoal\Charcoal as Charcoal;

/**
*
*/
class ViewLoader extends Loader
{
	private $_ident;

	/**
	*
	*/
	private $search_path = [];


	/**
	* @param string $ident
	* @throws \InvalidArgumentException if the ident is not a string
	* @return ViewLoader (Chainable)
	*/
	public function set_ident($ident)
	{
		if(!is_string($ident)) {
			throw new \InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.');
		}
		$this->_ident = $ident;
		return $this;
	}

	/**
	* @return string
	*/
	public function ident()
	{
		return $this->_ident;
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

		$this->search_path[] = $path;

		return $this;
	}

	/**
	* @return array
	*/
	public function search_path()
	{
		$cfg = Charcoal::$config;

		$all_path = $this->search_path;

		$global_path = isset($cfg['views_path']) ? $cfg['views_path'] : [];
		if(!empty($global_path)) {
			$all_path = Charcoal::merge($global_path, $all_path);
		}
		return $all_path;
	}

	/**
	* @return string
	*/
	public function load($ident=null)
	{
		if($ident !== null) {
			$this->set_ident($ident);
		}

		// Attempt loading from cache
		$ret = $this->_load_from_cache();
		if($ret !== false) {
			return $ret;
		}

		$data = '';
		$filename = $this->_filename_from_ident($ident);
		$search_path = $this->search_path();
		foreach($search_path as $path) {
			$f = $path.DIRECTORY_SEPARATOR.$filename;
			if(!file_exists($f)) {
				continue;
			}
			$file_content = file_get_contents($f);
			if($file_content !== '') {
				$data = $file_content;
				break;
			}
		}
		
		$this->_cache($data);

		return $data;
	}

	/**
	* @param string
	*
	* @return string
	*/
	private function _filename_from_ident($ident)
	{
		$filename = str_replace(['\\'], '.', $ident);
		$filename .= '.php';

		return $filename;

	}
}