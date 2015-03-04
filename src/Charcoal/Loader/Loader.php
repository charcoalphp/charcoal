<?php

namespace Charcoal\Loader;

abstract class Loader
{
	const DEFAULT_USE_CACHE = true;
	const DEFAULT_CACHE_TTS = 0;

	private $_cache_key;
	private $_source;

	private $_use_cache = self::DEFAULT_USE_CACHE;

	public function set_cache_key($cache_key)
	{
		if(!is_string($cache_key)) {
			throw new \InvalidArgumentException('Cache key must be a string');
		}

		$this->_cache_key = $cache_key;
		return $this;
	}

	public function cache_key()
	{
		if($this->_cache_key === null) {
			$this->_cache_key = '';
		}

		return $this->_cache_key;	
	}

	public function set_source(\Charcoal\Model\Source $source)
	{
		$this->_source = $source;
		return $this;
	}

	public function source()
	{
		if(!$this->_source) {
			return new \Charcoal\Model\Source();
		}
		return $this->_source;
	}

	public function set_use_cache($use_cache=self::DEFAULT_USE_CACHE)
	{
		if(!is_bool($use_cache)) {
			throw new \InvalidArgumentException('Param must be boolean');
		}
		$this->_use_cache = $use_cache;
	}

	/**
	* Return true if a cache_key has been set
	*/
	public function use_cache()
	{
		$cache_key = $this->cache_key();
		if($cache_key == '') {
			return false;
		}
		return !!$this->_use_cache;
	}

	abstract public function load();

	protected function _load_from_cache()
	{
		if(!$this->use_cache()) {
			return false;
		}
		$cache_key = $this->cache_key();
		return \Charcoal\Helper\Cache::get()->fetch($cache_key);
	}

	protected function _cache($data, $ttl=0)
	{
		if(!$this->use_cache()) {
			return false;
		}
		$cache_key = $this->cache_key();
		return \Charcoal\Helper\Cache::get()->store($cache_key, $data, $ttl);
	}


}