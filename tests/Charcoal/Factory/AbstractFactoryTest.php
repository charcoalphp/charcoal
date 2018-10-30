<?php

namespace Charcoal\Tests\Factory;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

use Charcoal\Factory\AbstractFactory;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractFactoryTest extends AbstractTestCase
{
    /**
     * @var AbstractFactory
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
     * @return void
     */
    public function testConstructor()
    {
        $obj = $this->getMockForAbstractClass(AbstractFactory::class, [[
            'base_class' => DateTimeInterface::class,
            'default_class' => DateTime::class,
            'arguments' => ['2018-01-01 15:30:00'],
            'map' => [
                'foo' => DateTime::class
            ]
        ]]);
        $this->assertEquals(DateTimeInterface::class, $obj->baseClass());
        $this->assertEquals(DateTime::class, $obj->defaultClass());

        $ret = $obj->create('foo');
        $this->assertInstanceOf(DateTime::class, $ret);
        $this->assertEquals('2018-01-01 15:30:00', $ret->format('Y-m-d H:i:s'));
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
     * @return void
     */
    public function testSetBaseClass()
    {
        $obj = $this->obj;
        $this->assertSame('', $obj->baseClass());

        $ret = $obj->setBaseClass(AbstractFactory::class);
        $this->assertSame($ret, $obj);
        $this->assertEquals(AbstractFactory::class, $obj->baseClass());

        $this->expectException(InvalidArgumentException::class);
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
     * @return void
     */
    public function testSetDefaultClass()
    {
        $this->assertSame('', $this->obj->defaultClass());

        $ret = $this->obj->setDefaultClass(AbstractFactory::class);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(AbstractFactory::class, $this->obj->defaultClass());

        $this->expectException(InvalidArgumentException::class);
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
