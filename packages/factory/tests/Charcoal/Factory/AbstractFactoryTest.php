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
    protected function setUp(): void
    {
        $this->obj = $this->getMockForAbstractClass(AbstractFactory::class);
    }

    /**
     * @return void
     */
    public function testConstructorBaseClassAndDefaultClass()
    {
        $obj = $this->getMockForAbstractClass(AbstractFactory::class, [[
            'base_class' => DateTimeInterface::class,
            'default_class' => DateTime::class
        ]]);
        $this->assertEquals(DateTimeInterface::class, $obj->baseClass());
        $this->assertEquals(DateTime::class, $obj->defaultClass());
    }

    /**
     * @return void
     */
    public function testConstructorArguments()
    {
        $obj = $this->getMockForAbstractClass(AbstractFactory::class, [[
            'arguments' => ['2018-01-01 15:30:00']
        ]]);
        $ret = $obj->create(DateTime::class);
        $this->assertEquals('2018-01-01 15:30:00', $ret->format('Y-m-d H:i:s'));
    }

    /**
     * @return void
     */
    public function testConstructorMap()
    {
        $obj = $this->getMockForAbstractClass(AbstractFactory::class, [[
            'map' => [
                'foo' => DateTime::class
            ]
        ]]);

        $ret = $obj->create('foo');
        $this->assertInstanceOf(DateTime::class, $ret);

        $this->expectException(InvalidArgumentException::class);
        $obj2 = $this->getMockForAbstractClass(AbstractFactory::class, [[
            'map' => [DateTime::class]
        ]]);
    }

    /**
     * @return void
     */
    public function testConstructorCallback()
    {
        $obj = $this->getMockForAbstractClass(AbstractFactory::class, [[
            'callback' => function ($obj) {
                $obj->setDate(2015, 7, 8);
                $obj->setTime(11, 59, 59);
                return $obj;
            }
        ]]);

        $ret = $obj->create(DateTime::class);
        $this->assertEquals('2015-07-08 11:59:59', $ret->format('Y-m-d H:i:s'));
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
     * @return void
     */
    public function testSetBaseClassNotAString()
    {
        $this->expectException(InvalidArgumentException::class);
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
     * Also asserts that subsequent call to `create()`:
     * - Create an instance of the default class if an invalid parameters is sent.
     *
     * @return void
     */
    public function testSetDefaultClass()
    {
        $this->assertSame('', $this->obj->defaultClass());

        $ret = $this->obj->setDefaultClass(DateTime::class);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(DateTime::class, $this->obj->defaultClass());

        $ret = $this->obj->create('foo');
        $this->assertInstanceOf(DateTime::class, $ret);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setDefaultClass('foobar');
    }

    /**
     * @return void
     */
    public function testSetDefaultClassNotAString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setDefaultClass(false);
    }


    /**
     * Asserts that the create method:
     * - Creates an object of the given class.
     * - Returns a new object on every call.
     *
     * @return void
     */
    public function testCreate()
    {
        $ret = $this->obj->create(DateTime::class);
        $this->assertInstanceOf(DateTime::class, $ret);

        $ret2 = $this->obj->create(DateTime::class);
        $this->assertNotSame($ret, $ret2);
    }

    /**
     * @return void
     */
    public function testCreateInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->create(false);
    }

    /**
     * Asserts that the get method:
     * - Returns an object of the given class.
     * - Returns the exact same object if called multiple times.
     *
     * @return void
     */
    public function testGet()
    {
        $ret = $this->obj->get(DateTime::class);
        $this->assertInstanceOf(DateTime::class, $ret);

        $ret2 = $this->obj->get(DateTime::class);
        $this->assertSame($ret, $ret2);
    }

    /**
     * @return void
     */
    public function testGetInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->get(false);
    }

    /**
     * @return void
     */
    public function testDefaultResolver()
    {
        $ret = $this->obj->create('date-time');
        $this->assertInstanceOf(DateTime::class, $ret);
    }
}
