<?php

namespace Charcoal\Tests\Cache;

use \Charcoal\Cache\CacheFactory as CacheFactory;

class CacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $obj = new CacheFactory();
        $this->assertInstanceOf('\Charcoal\Cache\CacheFactory', $obj);
    }
}
