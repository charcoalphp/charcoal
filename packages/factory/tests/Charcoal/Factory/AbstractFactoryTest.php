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

    protected function setUp(): void
    {
        $this->obj = new class extends AbstractFactory {};
    }

    public function testConstructorBaseClassAndDefaultClass(): void
    {
        $obj = new class ([
            'base_class' => DateTimeInterface::class,
            'default_class' => DateTime::class
        ]) extends AbstractFactory {};
        $this->assertEquals(DateTimeInterface::class, $obj->baseClass());
        $this->assertEquals(DateTime::class, $obj->defaultClass());
    }

    public function testConstructorArguments(): void
    {
        $obj = new class ([
            'arguments' => ['2018-01-01 15:30:00']
        ]) extends AbstractFactory {};
        $ret = $obj->create(DateTime::class);
        $this->assertEquals('2018-01-01 15:30:00', $ret->format('Y-m-d H:i:s'));
    }

    public function testConstructorMap(): void
    {
        $obj = new class ([
            'map' => [
                'foo' => DateTime::class
            ]
        ]) extends AbstractFactory {};

        $ret = $obj->create('foo');
        $this->assertInstanceOf(DateTime::class, $ret);

        $this->expectException(InvalidArgumentException::class);
        new class ([
            'map' => [DateTime::class]
        ]) extends AbstractFactory {};
    }

    public function testConstructorCallback(): void
    {
        $obj = new class ([
            'callback' => function ($obj) {
                $obj->setDate(2015, 7, 8);
                $obj->setTime(11, 59, 59);
                return $obj;
            }
        ]) extends AbstractFactory {};

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
     */
    public function testSetBaseClass(): void
    {
        $obj = $this->obj;
        $this->assertSame('', $obj->baseClass());

        $ret = $obj->setBaseClass(AbstractFactory::class);
        $this->assertSame($ret, $obj);
        $this->assertEquals(AbstractFactory::class, $obj->baseClass());

        $this->expectException(InvalidArgumentException::class);
        $obj->setBaseClass('foobar');
    }

    public function testSetBaseClassNotAString(): void
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
     */
    public function testSetDefaultClass(): void
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

    public function testSetDefaultClassNotAString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setDefaultClass(false);
    }


    /**
     * Asserts that the create method:
     * - Creates an object of the given class.
     * - Returns a new object on every call.
     */
    public function testCreate(): void
    {
        $ret = $this->obj->create(DateTime::class);
        $this->assertInstanceOf(DateTime::class, $ret);

        $ret2 = $this->obj->create(DateTime::class);
        $this->assertNotSame($ret, $ret2);
    }

    public function testCreateInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->create(false);
    }

    /**
     * Asserts that the get method:
     * - Returns an object of the given class.
     * - Returns the exact same object if called multiple times.
     */
    public function testGet(): void
    {
        $ret = $this->obj->get(DateTime::class);
        $this->assertInstanceOf(DateTime::class, $ret);

        $ret2 = $this->obj->get(DateTime::class);
        $this->assertSame($ret, $ret2);
    }

    public function testGetInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->get(false);
    }

    public function testDefaultResolver(): void
    {
        $ret = $this->obj->create('date-time');
        $this->assertInstanceOf(DateTime::class, $ret);
    }
}
