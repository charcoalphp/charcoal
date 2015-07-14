<?php

namespace Charcoal\Tests\Cache;

use \Charcoal\Cache\CacheFactory as CacheFactory;

class CacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $obj = CacheFactory::instance();
        $this->assertInstanceOf('\Charcoal\Cache\CacheFactory', $obj);
    }

    public function testGet()
    {
        $obj = CacheFactory::instance();
        $noop = $obj->get('noop');
        $this->assertInstanceOf('\Charcoal\Cache\Noop\NoopCache', $noop);

        $apc = $obj->get('apc');
        $this->assertInstanceOf('\Charcoal\Cache\Apc\ApcCache', $apc);

        $memcache = $obj->get('memcache');
        $this->assertInstanceOf('\Charcoal\Cache\Memcache\MemcacheCache', $memcache);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->get(false);
    }

    public function testGetInvalid()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = CacheFactory::instance();
        $obj->get('foobar');
    }
}
