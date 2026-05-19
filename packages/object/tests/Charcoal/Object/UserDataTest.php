<?php

namespace Charcoal\Tests\Object;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\UserData;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;

/**
 *
 */
class UserDataTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Object\UserData $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = $container['model/factory']->create(UserData::class);
    }

    public function testDefaults(): void
    {
        $this->assertNull($this->obj['ip']);
        $this->assertNull($this->obj['lang']);
        $this->assertNull($this->obj['ts']);
    }

    public function testSetData(): void
    {
        $ret = $this->obj->setData([
            'ip'=>'192.168.1.1',
            'lang'=>'fr',
            'ts'=>'2015-01-01 15:05:20'
        ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(ip2long('192.168.1.1'), $this->obj['ip']);
        $this->assertEquals('fr', $this->obj['lang']);
        $expected = new DateTime('2015-01-01 15:05:20');
        $this->assertEquals($expected, $this->obj['ts']);
    }

    public function testSetIp(): void
    {
        $ret = $this->obj->setIp('1.1.1.1');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(ip2long('1.1.1.1'), $this->obj['ip']);

        $this->obj->setIp(2349255);
        $this->assertEquals(2349255, $this->obj['ip']);
    }

    public function testSetLang(): void
    {
        $ret = $this->obj->setLang('en');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('en', $this->obj['lang']);

        $this->expectException('\InvalidArgumentException');
        $this->obj->setLang(false);
    }

    public function testSetTs(): void
    {
        $ret = $this->obj->setTs('July 1st, 2014');
        $this->assertSame($ret, $this->obj);
        $expected = new DateTime('July 1st, 2014');
        $this->assertEquals($expected, $this->obj['ts']);

        $this->expectException('\InvalidArgumentException');
        $this->obj->setTs(false);
    }

    /**
     * Set up the service container.
     */
    private function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
