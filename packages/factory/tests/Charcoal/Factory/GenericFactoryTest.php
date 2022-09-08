<?php

namespace Charcoal\Tests\Factory;

use Charcoal\Factory\GenericFactory;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericFactoryTest extends AbstractTestCase
{
    /**
     * @var GenericFactory
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = new GenericFactory();
    }

    /**
     * @return void
     */
    public function testIsResolvable()
    {
        $this->assertTrue($this->obj->isResolvable('DateTime'));
        $this->assertFalse($this->obj->isResolvable('foobaz'));

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->isResolvable(false);
    }

    /**
     * @return void
     */
    public function testCreate()
    {
        $ret = $this->obj->create('\DateTime');
        $this->assertInstanceOf('\DateTime', $ret);

        $this->expectException(\Exception::class);
        $ret2 = $this->obj->create('foobar');
    }

    /**
     * Asserts that the AbstractFactory's `create()` method, as GenericFactory:
     * - Returns the default class when passing an invalid argument, if set
     * - Throws an exception when passing an invalid argument, if no default class is set
     *
     * @return void
     */
    public function testCreateDefaultClass()
    {
        $this->obj->setDefaultClass('\DateTime');
        $ret = $this->obj->create('foobar');
        $this->assertInstanceOf('\DateTime', $ret);
    }

    /**
     * @return void
     */
    public function testCreateCreatesNewInstance()
    {
        $ret1 = $this->obj->create('\DateTime');
        $ret2 = $this->obj->create('\DateTime');

        $this->assertNotSame($ret1, $ret2);
    }

    /**
     * @return void
     */
    public function testCreateCallback()
    {
        $ret = $this->obj->create('\DateTime', null, function($obj) {
            $this->assertInstanceOf('\DateTime', $obj);
        });
    }

    /**
     * @return void
     */
    public function testGetReturnsSameInstance()
    {
        $ret1 = $this->obj->get('\DateTime');
        $ret2 = $this->obj->get('\DateTime');

        $this->assertSame($ret1, $ret2);
    }

    /**
     * @return void
     */
    public function testCreateBaseClass()
    {
        $this->obj->setBaseClass('\DateTimeInterface');
        $ret = $this->obj->create('\DateTime');
        $this->assertInstanceOf('\DateTime', $ret);

        $this->expectException(\Exception::class);
        $this->obj->setBaseClass('\Charcoal\Factory\FactoryInterface');
        $this->obj->create('\DateTime');
    }
}
