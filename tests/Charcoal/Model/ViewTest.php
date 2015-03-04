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

}

