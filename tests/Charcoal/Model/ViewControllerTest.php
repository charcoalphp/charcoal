<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\ViewController as ViewController;
use \Charcoal\Model\Model as Model;

class ViewControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$model = new Model();
		$obj = new ViewController($model);
		$this->assertInstanceOf('\Charcoal\Model\ViewController', $obj);
	}

}

