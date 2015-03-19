<?php

namespace Charcoal\Model;

use \Charcoal\Model\Model as Model;

/**
* Model View\Controller
*/
class ViewController
{
	/**
	* @var \Charcoal\Model\Model $model
	*/
	private $_model;

	/**
	*
	*/
	static public function get(Model $model=null)
	{
		// Todo [Mat. 2015-02-27] Load custom controller for specific model if it exists. 
		$model_controller = new ViewController($model);
		return $model_controller;
	}

	static public function from_ident($ident)
	{

	}

	/**
	*
	*/
	public function __construct(Model $model=null)
	{
		$this->_model = $model;
	}

	/**
	* The Model View\Controller is a decorator around the Model.
	*
	* Because of (Mustache) template engine limitation, this also check for methods 
	* because `__call()` can not be used 
	*
	* @param string $name
	*
	* @return mixed
	* @see https://github.com/bobthecow/mustache.php/wiki/Magic-Methods
	*/
	public function __get($name)
	{

		$model = $this->_model();
		if($model === null) {
			return null;
		}

		// Try methods
		if(is_callable([$model, $name])) {
			return call_user_func([$model, $name]);
		}
		// Try Properties
		if(isset($model->{$name})) {
			return $model->{$name};
		}
		return null;
	}

	/**
	* The Model View\Controller is a decorator around the Model
	*
	* @param string $name
	* @param mixed $arguments
	*
	* @return mixed
	*/
	public function __call($name, $arguments)
	{
		$model = $this->_model();
		if($model === null) {
			return null;
		}

		if(is_callable([$model, $name])) {
			return call_user_func_array([$model, $name], $arguments);
		}

		return null;
	}
	
	/**
	* @param string $name
	*
	* @return boolean
	*/
	public function __isset($name)
	{
		$model = $this->_model();
		if($model === null) {
			return false;
		}

		// Try methods
		if(is_callable([$model, $name])) {
			return true;
		}

		// Try Properties
		if(isset($model->{$name})) {
			return true;
		}
		return false;
	}

	private function _model()
	{
		return $this->_model;
	}

}