<?php

namespace Charcoal\Tests\Factory;

use Charcoal\Factory\AbstractFactory;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractFactoryTest extends AbstractTestCase
{
    /**
     * @var ResolverFactory
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass(AbstractFactory::class);
    }

    /**
     * Assert that the `baseClass()` method:
     * - Defaults to ''
     * - Returns the proper value when the `baseClass is set
     * and that the `setBaseClass()` method:
     * - Is chainable
     * - Properly sets the baseClass value.
     * - Throws an exception if the parameter is not a valid (existing) class
     *
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testSetBaseClass()
    {
        $obj = $this->obj;
        $this->assertSame('', $obj->baseClass());

        $ret = $obj->setBaseClass(AbstractFactory::class);
        $this->assertSame($ret, $obj);
        $this->assertEquals(AbstractFactory::class, $obj->baseClass());

        $obj->setBaseClass('foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testSetBaseClassNotAString()
    {
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
     *
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testSetDefaultClass()
    {
        $this->assertSame('', $this->obj->defaultClass());

        $ret = $this->obj->setDefaultClass(AbstractFactory::class);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(AbstractFactory::class, $this->obj->defaultClass());

        $this->obj->setDefaultClass('foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testSetDefaultClassNotAString()
    {
        $this->obj->setDefaultClass(false);
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testCreateInvalidArgumentException()
    {
        $this->obj->create(false);
    }
}
