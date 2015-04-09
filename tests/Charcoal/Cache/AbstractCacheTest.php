<?php

namespace Charcoal\Tests\Cache;

use \Charcoal\Cache\Noop\NoopCache as NoopCache;

/**
* Test the AbstractCache concrete methods through the NoopCache
*/
class AbstractCacheTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->obj = new NoopCache();
    }

    public function testSetPrefix()
    {
        $obj = new NoopCache();

        $default = $obj->prefix();
        $expected_default = $obj->config()->prefix();
        $this->assertEquals($expected_default, $default);

        $ret = $obj->set_prefix('foo_');

        $this->assertSame($ret, $obj);
        $this->assertEquals('foo_', $obj->prefix());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_prefix([1, 2, 3]);
    }

    /*public function testEnabledTrueByDefault()
    {
        $obj = new NoopCache();

        $default = $obj->enabled();
        $this->assertEquals(true, $default);
    }*/
}
