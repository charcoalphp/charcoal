<?php

namespace Charcoal\Tests\Cache\Memcache;

use \Charcoal\Cache\Memcache\MemcacheCache as MemcacheCache;

class MemcacheCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = MemcacheCache::instance();
        $this->assertInstanceOf('\Charcoal\Cache\Memcache\MemcacheCache', $obj);
    }

    public function testEnabled()
    {
        $obj = MemcacheCache::instance();
        $ext_loaded = !!class_exists('\Memcache');
        $this->assertEquals($ext_loaded, $obj->enabled());

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->enabled());
    }

    public function testStore()
    {
        $obj = MemcacheCache::instance();
        $obj->config()->set_active(true);
        if ($obj->enabled() !== false) {
            $this->assertTrue($obj->store('foo', 'bar'));
        }

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->store('foo', 'bar'));
    }

    public function testExists()
    {
        $obj = MemcacheCache::instance();
        $obj->config()->set_active(true);
        if ($obj->enabled() !== false) {
            $this->assertTrue($obj->exists('foo'));
        }

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->exists('foo'));
    }

    public function testFetch()
    {
        $obj = MemcacheCache::instance();
        $obj->config()->set_active(true);
        if ($obj->enabled() !== false) {
            // Was set in cache in `testStore`
            $this->assertEquals('bar', $obj->fetch('foo'));
        }

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->fetch('foo'));
    }

    public function testMultifetch()
    {
        $obj = MemcacheCache::instance();
        $obj->config()->set_active(true);
        $obj->store('baz', 123);
        if ($obj->enabled() !== false) {
            // Was set in cache in `testStore`
            $this->assertEquals(['foo' => 'bar', 'baz' => 123], $obj->multifetch(['foo', 'baz']));
        }
        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->multifetch(['foo', 'baz']));
    }

    public function testDelete()
    {
        $obj = MemcacheCache::instance();
        $obj->config()->set_active(true);
        if ($obj->enabled() !== false) {
            $this->assertTrue($obj->delete('foo'));
        }

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->delete('baz'));
    }
}
