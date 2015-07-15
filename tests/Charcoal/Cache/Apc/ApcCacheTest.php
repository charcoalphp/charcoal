<?php

namespace Charcoal\Tests\Cache\Apc;

use \Charcoal\Cache\Apc\ApcCache as ApcCache;

class ApcCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = ApcCache::instance();
        $this->assertInstanceOf('\Charcoal\Cache\Apc\ApcCache', $obj);
    }

    public function testEnabled()
    {
        $obj = ApcCache::instance();
        $ext_loaded = !!extension_loaded('apc');
        $this->assertEquals($ext_loaded, $obj->enabled());

        $obj = ApcCache::instance();
        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->enabled());
    }

    public function testStore()
    {
        $obj = ApcCache::instance();
        $ret = $obj->store('foo', 'bar');
        if ($obj->enabled()) {
            $this->assertTrue($ret);
        } else {
            $this->assertNotTrue($ret);
        }
    }

    public function testExists()
    {
        $obj = ApcCache::instance();
        $ret = $obj->exists('foo');
        if ($obj->enabled()) {
            $this->assertTrue($ret);
        } else {
            $this->assertNotTrue($ret);
        }
    }

    public function testFetch()
    {
        $obj = ApcCache::instance();
        $ret = $obj->fetch('foo');
        if ($obj->enabled()) {
            // Was set in cache in `testStore`
            $this->assertEquals('bar', $ret);
        } else {
            $this->assertNotTrue($ret);
        }
    }

    public function testMultifetch()
    {
        $obj = ApcCache::instance();
        $obj->store('baz', 123);
        $ret = $obj->multifetch(['foo', 'baz']);
        if ($obj->enabled()) {
            // Was set in cache in `testStore`
            $this->assertEquals(['foo' => 'bar', 'baz' => 123], $ret);
        } else {
            $this->assertNotTrue($ret);
        }
    }

    public function testDelete()
    {
        $obj = ApcCache::instance();
        $ret = $obj->delete('foo');
        if ($obj->enabled()) {
            $this->assertTrue($ret);
        } else {
            $this->assertNotTrue($ret);
        }
    }
}
