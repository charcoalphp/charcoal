<?php

namespace Charcoal\Model\Validator;

class Result
{
	public $ident;
	public $level;
	public $message;
	public $ts;

	public function __construct($data=null)
	{
		$this->ts = time();

		if($data) {
			$this->set_data($data);
		}
	}

	public function set_data($data)
	{
		if(isset($data['ident'])) {
			$this->ident = $data['ident'];
		}

		if(isset($data['level'])) {
			$this->level = $data['level'];
		}

		if(isset($data['message'])) {
			$this->message = $data['message'];
		}
	}
}