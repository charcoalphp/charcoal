<?php

namespace Charcoal\Tests\Object;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\ObjectSchedule;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;

/**
 *
 */
class ObjectScheduleTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Object\ObjectSchedule $obj;

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

        $this->obj = $container['model/factory']->create(ObjectSchedule::class);
    }

    public function testSetTargetType(): void
    {
        $this->assertNull($this->obj->getTargetType());
        $ret = $this->obj->setTargetType('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->getTargetType());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setTargetType(false);
    }

    public function testSetTargetId(): void
    {
        $this->assertNull($this->obj->getTargetId());
        $ret = $this->obj->setTargetId(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->getTargetId());
    }

    public function testSetDataDiff(): void
    {
        $this->assertEquals([], $this->obj->getDataDiff());
        $ret = $this->obj->setDataDiff(['foo'=>42]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'=>42], $this->obj->getDataDiff());
    }

    public function testSetProcessed(): void
    {
        $this->assertFalse($this->obj->getProcessed());
        $ret = $this->obj->setProcessed(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->getProcessed());
    }

    public function testSetScheduledDate(): void
    {
        $obj = $this->obj;
        $this->assertNull($obj->getScheduledDate());
        $ret = $obj->setScheduledDate('2015-01-01 13:05:45');
        $this->assertSame($ret, $obj);
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $obj->getScheduledDate());

        $obj->setScheduledDate(null);
        $this->assertNull($obj->getScheduledDate());

        $this->expectException('\InvalidArgumentException');
        $obj->setScheduledDate(false);
    }

    public function testSetScheduledDateInvalidTime(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->setScheduledDate('A totally invalid date time');
    }

    public function testSetProcessedDate(): void
    {
        $obj = $this->obj;
        $this->assertNull($obj->getProcessedDate());
        $ret = $obj->setProcessedDate('2015-01-01 13:05:45');
        $this->assertSame($ret, $obj);
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $obj->getProcessedDate());

        $obj->setProcessedDate(null);
        $this->assertNull($obj->getProcessedDate());

        $this->expectException('\InvalidArgumentException');
        $obj->setProcessedDate(false);
    }

    public function testSetProcessedDateInvalidTime(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->setProcessedDate('A totally invalid date time');
    }

    public function testProcess(): void
    {
        $container = $this->container();
        $this->obj->setModelFactory($container['model/factory']);

        $this->assertFalse($this->obj->process());

        $this->obj->setTargetType('charcoal/object/content');
        $this->assertFalse($this->obj->process());

        $this->obj->setTargetId(42);
        $this->assertFalse($this->obj->process());

        //q$this->obj->process();
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
