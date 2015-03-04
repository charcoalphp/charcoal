<?php
/**
*
*/

namespace Charcoal\Loader;
use \Charcoal\Charcoal as Charcoal;

/**
*
*/
class MetadataLoader extends Loader
{

	private $properties;
	private $data;

	/**
	*
	*/
	private $search_path = [];

	/**
	* @param string $path
	*
	* @throws \InvalidArgumentException if the path does not exist or is invalid
	* @return \Charcoal\Service\Loader\Metadata (Chainable)
	*/
	public function add_path($path)
	{
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
		return $this->search_path;
	}

	/**
	*
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

		$hierarchy = $this->_hierarchy();

		$metadata = [];
		foreach($hierarchy as $id) {
			$ident_data = self::_load_ident($id);
			if(is_array($ident_data)) {
				$metadata = Charcoal::merge($metadata, $ident_data);
			}
		}

		$this->_cache($metadata);

		return $metadata;
	}

	/**
	* @return array
	*/
	private function _hierarchy()
	{
		$ident = $this->ident();
		$hierarchy = null;

		if(class_exists($ident)) {
			// If the object is a class, we use hierarchy from object ancestor classes
		//	pre('=='.$ident);
			$p = $ident;
			$ident_hierarchy = [$p];

			// Also load class' traits, if any
			$traits = class_uses($ident);
			foreach($traits as $trait) {
				$ident_hierarchy[] = $trait;
			}
			//pre($p);
			while($p = get_parent_class($p)) {
				$ident_hierarchy[] = $p;

				// Also load parent classes' traits, if any
				$traits = class_uses($p);
				foreach($traits as $trait) {
					//pre($trait);
					$ident_hierarchy[] = $trait;
				}
			}
			
			$ident_hierarchy = array_reverse($ident_hierarchy);
		}
		else {
			if(is_array($hierarchy) && !empty($hierarchy)) {
				$hierarchy[] = $ident;
				$ident_hierarchy = $hierarchy;
			}
			else {
				$ident_hierarchy = [$ident];
			}
		}

		return $ident_hierarchy;
	}

	/**
	* Get an "ident" (file) from all search path and merge the content
	*
	* @param string $ident
	*
	* @return array
	*/
	private function _load_ident($ident)
	{
		$data = [];
		$filename = $this->_filename_from_ident($ident);
		$search_path = $this->search_path();
		foreach($search_path as $path) {
			$f = $path.DIRECTORY_SEPARATOR.$filename;
			if(!file_exists($f)) {
				continue;
			}
			$file_content = file_get_contents($f);
			if($file_content === '') {
				continue;
			}
			// Decode as an array (2nd parameter, true = array)
			$file_data = json_decode($file_content, true);
			// Todo: Handle json_last_error()
			if(is_array($file_data)) {
				$data = Charcoal::merge($data, $file_data);
			}
		}

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
		$filename .= '.json';

		return $filename;

	}

}