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
    * Assert that the `base_class()` method:
    * - Defaults to ''
    * - Returns the proper value when the `base_class is set
    * and that the `set_base_class()` method:
    * - Is chainable
    * - Properly sets the base_class value.
    * - Throws an exception if the parameter is not a valid (existing) class
    */
    public function testSetBaseClass()
    {
        $obj = $this->obj;
        $this->assertSame('', $obj->base_class());

        $ret = $obj->set_base_class('\Charcoal\Factory\AbstractFactory');
        $this->assertSame($ret, $obj);
        $this->assertEquals('\Charcoal\Factory\AbstractFactory', $obj->base_class());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_base_class('foobar');
    }

    /**
    * Assert that the `default_class()` method:
    * - Defaults to ''
    * - Returns the proper value when the `default_class is set
    * and that the `set_default_class()` method:
    * - Is chainable
    * - Properly sets the default_class value.
    * - Throws an exception if the parameter is not a valid (existing) class
    */
    public function testSetDefaultClass()
    {
        $obj = $this->obj;
        $this->assertSame('', $obj->default_class());

        $ret = $obj->set_default_class('\Charcoal\Factory\AbstractFactory');
        $this->assertSame($ret, $obj);
        $this->assertEquals('\Charcoal\Factory\AbstractFactory', $obj->default_class());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_default_class('foobar');
    }
}
