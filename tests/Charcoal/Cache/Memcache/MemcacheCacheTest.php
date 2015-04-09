<?php

namespace Charcoal\Tests\Cache\Memcache;

use \Charcoal\Cache\Memcache\MemcacheCache as MemcacheCache;

class MemcacheCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = new MemcacheCache();
        $this->assertInstanceOf('\Charcoal\Cache\Memcache\MemcacheCache', $obj);
    }
}
