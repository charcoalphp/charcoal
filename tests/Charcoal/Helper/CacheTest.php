<?php

namespace Charcoal\Tests\Helper;

use \Charcoal\Helper\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
	public function testContructor()
	{
		$obj = new Cache();
		$this->assertInstanceOf('\Charcoal\Helper\Cache', $obj);
	}
}
