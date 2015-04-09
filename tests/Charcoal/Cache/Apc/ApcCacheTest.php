<?php

namespace Charcoal\Tests\Cache\Apc;

use \Charcoal\Cache\Apc\ApcCache as ApcCache;

class ApcCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = new ApcCache();
        $this->assertInstanceOf('\Charcoal\Cache\Apc\ApcCache', $obj);
    }
}
