<?php

namespace Charcoal\Model;

use Charcoal\Model\Validator\Result as Result;

class Validator
{

	const ERROR 	= 'error';
	const WARNING 	= 'warning';
	const NOTICE 	= 'notice';

	private $_model;

	private $_results;

	public function __construct(Model $model)
	{
		$this->_model = $model;
		$this->_results = [];
	}

	public function error($msg)
	{
		return $this->log(self::ERROR, $msg);
	}

	public function warning($msg)
	{
		return $this->log(self::WARNING, $msg);
	}

	public function notice($msg)
	{
		return $this->log(self::NOTICE, $msg);
	}

	public function log($level, $msg)
	{
		if(!isset($this->_results[$level])) {
			$this->_results[$level] = [];
		}
		$log = new Result([
			'ident'=>'',
			'level'=>$level,
			'message'=>$msg
		]);
		$this->_results[$level][] = $log;
	}

	public function results()
	{
		return $this->_results;
	}

	public function merge(Validator $v, $ident)
	{
		$results = $v->results();
		foreach($results as $level => $res) {
			foreach($res as $r) {
				$r->ident = $ident;
				$this->_results[$level][] = $r;
			}
		}
	}

	public function validate()
	{
		$model = $this->_model;

		$model->validate($this);

		$props = $model->properties();

		foreach($props as $ident => $p) {

			if(!$p->active()) {
				continue;
			}

			$property_validator = $p->validator()->validate_model($p);
			$this->merge($property_validator, $ident);
		}

		return $this;
	}
}