<?php

namespace Charcoal\Tests\Core;

use \Charcoal\Factory\AbstractFactory;

use \Charcoal\Tests\Factory\AbstractFactoryClass as AbstractFactoryClass;

/**
 *
 */
class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Factory\AbstractFactory');
    }

    /**
     * Assert that the `baseClass()` method:
     * - Defaults to ''
     * - Returns the proper value when the `baseClass is set
     * and that the `setBaseClass()` method:
     * - Is chainable
     * - Properly sets the baseClass value.
     * - Throws an exception if the parameter is not a valid (existing) class
     */
    public function testSetBaseClass()
    {
        $obj = $this->obj;
        $this->assertSame('', $obj->baseClass());

        $ret = $obj->setBaseClass('\Charcoal\Factory\AbstractFactory');
        $this->assertSame($ret, $obj);
        $this->assertEquals('\Charcoal\Factory\AbstractFactory', $obj->baseClass());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setBaseClass('foobar');
    }

    public function testSetBaseClassNotAString()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setBaseClass(false);
    }

    /**
     * Assert that the `defaultClass()` method:
     * - Defaults to ''
     * - Returns the proper value when the `defaultClass is set
     * and that the `setDefaultClass()` method:
     * - Is chainable
     * - Properly sets the defaultClass value.
     * - Throws an exception if the parameter is not a valid (existing) class
     */
    public function testSetDefaultClass()
    {
        $this->assertSame('', $this->obj->defaultClass());

        $ret = $this->obj->setDefaultClass('\Charcoal\Factory\AbstractFactory');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('\Charcoal\Factory\AbstractFactory', $this->obj->defaultClass());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setDefaultClass('foobar');
    }

    public function testSetDefaultClassNotAString()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setDefaultClass(false);
    }

    public function testCreateInvalidArgumentException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->create(false);
    }
}
