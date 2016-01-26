<?php

namespace Charcoal\Tests\Core;

/**
 *
 */
class GenericFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $this->obj = new \Charcoal\Factory\GenericFactory();
    }

    /**
     * Asserts that the resolve method:
     * - Simply returns the parameter, as is.
     * - Throws an exception if the parameter is not a string
     */
    public function testResolve()
    {
        $ret = $this->obj->resolve('foo');
        $this->assertEquals('foo', $ret);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->resolve(false);
    }

    public function testIsResolvable()
    {
        $this->assertTrue($this->obj->isResolvable('DateTime'));
        $this->assertFalse($this->obj->isResolvable('foobaz'));

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->isResolvable(false);
    }

    public function testCreate()
    {
        $ret = $this->obj->create('\DateTime');
        $this->assertInstanceOf('\DateTime', $ret);

        $this->setExpectedException('\Exception');
        $ret2 = $this->obj->create('foobar');
    }

    /**
     * Asserts that the AbstractFactory's `create()` method, as GenericFactory:
     * - Returns the default class when passing an invalid argument, if set
     * - Throws an exception when passing an invalid argument, if no default class is set
     */
    public function testCreateDefaultClass()
    {
        $this->obj->setDefaultClass('\DateTime');
        $ret = $this->obj->create('foobar');
        $this->assertInstanceOf('\DateTime', $ret);

        // $this->obj->setDefaultClass(get_class($this));
        // $this->setExpectedException('\Exception');
        // $this->obj->create('foobar');
    }


    public function testCreateCreatesNewInstance()
    {
        $ret1 = $this->obj->create('\DateTime');
        $ret2 = $this->obj->create('\DateTime');

        $this->assertNotSame($ret1, $ret2);
    }

    public function testCreateCallback()
    {
        $ret = $this->obj->create('\DateTime', null, function($obj) {
            $this->assertInstanceOf('\DateTime', $obj);
        });
    }

    public function testGetReturnsSameInstance()
    {
        $ret1 = $this->obj->get('\DateTime');
        $ret2 = $this->obj->get('\DateTime');

        $this->assertSame($ret1, $ret2);
    }

    public function testCreateBaseClass()
    {
        $this->obj->setBaseClass('\DateTimeInterface');
        $ret = $this->obj->create('\DateTime');
        $this->assertInstanceOf('\DateTime', $ret);

        $this->obj->setBaseClass('\Charcoal\Factory\FactoryInterface');
        $this->setExpectedException('\Exception');
        $this->obj->create('\DateTime');
    }
}
