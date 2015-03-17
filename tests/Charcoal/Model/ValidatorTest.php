<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\Validator;
use \Charcoal\Model\Model;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		$model = new Model();
		$obj = new Validator($model);
		$this->assertInstanceOf('\Charcoal\Model\Validator', $obj);
	}

	public function testValidateModel()
	{
		
		$model = new Model();
		$model->set_metadata([
			'properties'=>[
				'foo'=>[
					'type'=>'string',
					'required'=>true,
					'min_length'=>5
				]
			]
		]);

		$obj = new Validator($model);
		$ret = $obj->validate();
		
		$this->assertSame($ret, $obj);
	}
}
