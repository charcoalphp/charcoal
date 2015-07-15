<?php

namespace Charcoal\Tests\Cache;

use \Charcoal\Cache\CacheConfig as CacheConfig;

class CacheConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = new CacheConfig();
        $this->assertInstanceOf('\Charcoal\Cache\CacheConfig', $obj);
    }

    public function testDefaultData()
    {
        $obj = new CacheConfig();
        $defaults = $obj->default_data();

        /*
        $this->assertEquals($obj->active(), $defaults['active']);
        $this->assertEquals($obj->type(), $defaults['type']);
        $this->assertEquals($obj->default_ttl(), $defaults['default_ttl']);
        $this->assertEquals($obj->prefix(), $defaults['prefix']);
        */
    }

    public function testSetActive()
    {
        $obj = new CacheConfig();
        $ret = $obj->set_active(false);

        $this->assertSame($obj, $ret);
        $this->assertEquals(false, $obj->active());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_active([1,2,3]);
    }

    public function testSetType()
    {
        $obj = new CacheConfig();
        $ret = $obj->set_type('foo');

        $this->assertSame($obj, $ret);
        $this->assertEquals('foo', $obj->type());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_type([1,2,3]);
    }
}
