<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\Property;

class PropertyTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Property();
		$this->assertInstanceOf('\Charcoal\Model\Property', $obj);
	}

	/**
	* Hello world
	*/
	public function testSetValGetVal()
	{
		$obj = new Property();
		
		$obj->set_val('foo');
		$this->assertEquals('foo', $obj->val());
	}

}

