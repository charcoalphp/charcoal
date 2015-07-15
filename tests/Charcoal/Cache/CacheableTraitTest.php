<?php

namespace Charcoal\Tests\Cache;

use \Charcoal\Cache\CacheFactory as CacheFactory;

class CacheableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public static function setUpBeforeClass()
    {
        include 'CacheableClass.php';
    }

    public function setUp()
    {
        $this->obj = new CacheableClass();
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Tests\Cache\CacheableClass', $obj);
    }

    public function testSetCache()
    {
        $obj = $this->obj;
        $cache = CacheFactory::instance()->get('noop');
        $ret = $obj->set_cache($cache);
        $this->assertSame($ret, $obj);
        $this->assertSame($cache, $obj->cache());
    }

    public function testSetCacheKey()
    {
        $obj = $this->obj;
        $ret = $obj->set_cache_key('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->cache_key());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_cache_key(false);
    }
}
