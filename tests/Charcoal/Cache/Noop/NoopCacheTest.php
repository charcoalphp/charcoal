<?php

namespace Charcoal\Tests\Cache\Noop;

use \Charcoal\Cache\Noop\NoopCache as NoopCache;

class NoopCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = new NoopCache();
        $this->assertInstanceOf('\Charcoal\Cache\Noop\NoopCache', $obj);
    }

    public function testAll()
    {
        $obj = new NoopCache();

        $this->assertEquals(true, $obj->init());
        $this->assertEquals(false, $obj->exists('foo'));
        $this->assertEquals(true, $obj->store('foo', 'bar'));
        $this->assertEquals(null, $obj->fetch('foo'));
        $this->assertEquals(null, $obj->multifetch('foo'));
        $this->assertEquals(true, $obj->delete('foo'));
        $this->assertEquals(true, $obj->clear());
    }
}
