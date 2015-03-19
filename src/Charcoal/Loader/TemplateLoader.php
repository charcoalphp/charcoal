<?php
/**
*
*/

namespace Charcoal\Loader;
use \Charcoal\Charcoal as Charcoal;

/**
*
*/
class TemplateLoader extends FileLoader
{
	private $_ident;

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
	* @return array
	*/
	public function search_path()
	{
		$cfg = Charcoal::config();

		$all_path = parent::search_path();

		$global_path = isset($cfg['templates_path']) ? $cfg['templates_path'] : [];
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
		if($ret !== null) {
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