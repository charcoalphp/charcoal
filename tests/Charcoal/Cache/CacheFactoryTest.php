<?php

namespace Charcoal\Tests\Cache;

use \Charcoal\Cache\CacheFactory as CacheFactory;

class CacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorByInstance()
    {
        $obj = CacheFactory::instance();
        $this->assertInstanceOf('\Charcoal\Cache\CacheFactory', $obj);
        return $obj;
    }

    public function testGetInvalidParamter()
    {
        $obj = CacheFactory::instance();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->get(false);
    }

    /**
    * @depends testConstructorByInstance
    */
    public function testGetNoop()
    {
        $obj = CacheFactory::instance();
        $noop = $obj->get('noop');
        $this->assertInstanceOf('\Charcoal\Cache\Noop\NoopCache', $noop);
    }

    /**
    * @depends testConstructorByInstance
    */
    public function testGetApc()
    {
        $obj = CacheFactory::instance();
        $apc = $obj->get('apc');
        $this->assertInstanceOf('\Charcoal\Cache\Apc\ApcCache', $apc);

    }

    /**
    * @depends testConstructorByInstance
    */
    public function testGetMemcache()
    {
        $obj = CacheFactory::instance();
        $memcache = $obj->get('memcache');
         $this->assertInstanceOf('\Charcoal\Cache\Memcache\MemcacheCache', $memcache);
    }


    public function testGetInvalid()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = CacheFactory::instance();
        $obj->get('foobar');
    }
}
