<?php

namespace Charcoal\Tests\Cache\Memcache;

use \Charcoal\Cache\Memcache\MemcacheCache as MemcacheCache;

class MemcacheCacheTest extends \PHPUnit_Framework_TestCase
{

    /**
    * Assert that the default `instance()` static method returns a valid object.
    */
    public function testContructorByInstance()
    {
        $obj = MemcacheCache::instance();
        $this->assertInstanceOf('\Charcoal\Cache\Memcache\MemcacheCache', $obj);
        return true;
    }

    /**
    * @depends testConstructorByInstance
    */
    public function testEnabled()
    {
        $obj = MemcacheCache::instance();
        $ext_loaded = !!class_exists('\Memcache');
        $this->assertEquals($ext_loaded, $obj->enabled());

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->enabled());
    }

    /**
    * @depends testEnabled
    */
    public function testStore()
    {
        $obj = MemcacheCache::instance();

        $obj->config()->set_active(true);
        $this->assertTrue($obj->store('foo', 'bar'));

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->store('foo', 'bar'));
    }

    /**
    * @depends testEnabled
    */
    public function testExists()
    {
        $obj = MemcacheCache::instance();
        $obj->config()->set_active(true);
        $this->assertTrue($obj->exists('foo'));

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->exists('foo'));
    }

    /**
    * @depends testEnabled
    */
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

    /**
    * @depends testEnabled
    */
    public function testMultifetch()
    {
        $obj = MemcacheCache::instance();
        $obj->config()->set_active(true);
        $obj->store('baz', 123);

        // "foo" Was set in cache in `testStore`
        $this->assertEquals(['foo' => 'bar', 'baz' => 123], $obj->multifetch(['foo', 'baz']));

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->multifetch(['foo', 'baz']));
    }

    /**
    * @depends testEnabled
    */
    public function testDelete()
    {
        $obj = MemcacheCache::instance();

        $obj->config()->set_active(true);
        $this->assertTrue($obj->delete('foo'));

        $obj->config()->set_active(false);
        $this->assertNotTrue($obj->delete('baz'));
    }
}
