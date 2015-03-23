<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\View as View;

class ViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new View();
		$this->assertInstanceOf('\Charcoal\Model\View', $obj);
	}

	public function testSetTemplate()
	{
		$obj = new View();
		$obj->set_template('foo');
		$this->assertEquals('foo', $obj->template());

	}

	public function testSetTemplateIsChainable()
	{
		$obj = new View();
		$ret = $obj->set_template('foo');
		$this->assertSame($ret, $obj);
	}

	/*public function testFromIdent()
	{
		$obj = new View();
		$obj->from_ident('foo');
	}*/

}

