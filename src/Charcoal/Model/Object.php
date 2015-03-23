<?php
/**
 * Charcoal Object
 *
 * @package Charcoal
 * @subpackage core
 *
 * @copyright (c) Locomotive 2007-2012
 * @author Mathieu Ducharme <mat@locomotive.ca>
 * @version 2012-06-28
 * @since Version 2012-03-01
 * @license LGPL
 */

namespace Charcoal\Model;

use Charcoal;

class Object extends Model
{
	const DEFAULT_KEY = 'id';

	private $_key;
	private $_id;
	private $_active = true;
	

	public function __construct($data=null)
	{
		// Use Model constructor...
		parent::__construct();

		if($data !== null) {
			$this->set_data($data);
		}

		// ... and add one option to set the primary key and this object table
		$metadata = $this->metadata();
		$key = isset($metadata['key']) ? $metadata['key'] : self::DEFAULT_KEY;
		$this->set_key($key);
	}

	public function set_data($data)
	{
		parent::set_data($data);

		if(isset($data['key'])) {
			$this->set_id($data['key']);
		}
		if(isset($data['id'])) {
			$this->set_id($data['id']);
		}
		if(isset($data['active'])) {
			$this->set_key($data['active']);
		}

		return $this;
	}

	public function set_key($key)
	{
		//pre($val);
		if(!is_scalar($key)) {
			throw new \InvalidArgumentException('Key argument must be scalar');
		}
		$this->_key = $key;

		return $this;
	}

	public function key()
	{
		return $this->_key;
	}
	
	public function set_id($val)
	{
		if(!is_scalar($val)) {
			throw new \InvalidArgumentException('Val argument must be scalar');
		}
		$this->{$this->_key} = $val;

		// Chainable
		return $this;
	}

	public function id()
	{
		try {
			$key = $this->key();
		}
		catch(Exception $e) {
			return null;
		}
		if(!isset($this->{$key})) {
			throw new \Exception('Can not call id() - no key defined.');
		}
		return $this->{$key};
	}

	public function set_active($active)
	{
		if(!is_bool($active)) {
			throw new \InvalidArgumentException('active parameter needs to be bool');
		}
		$this->_active = $active;
		return $this;
	}
	public function active()
	{
		return $this->_active;
	}


}
