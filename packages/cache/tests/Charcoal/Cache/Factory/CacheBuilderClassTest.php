<?php

namespace Charcoal\Tests\Cache\Factory;

use InvalidArgumentException;
// From 'tedivm/stash'
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;
use Charcoal\Cache\CacheBuilder;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test constructor and class attributes from the CacheBuilder.
 */
#[CoversClass(CacheBuilder::class)]
class CacheBuilderClassTest extends AbstractCacheBuilderTestCase
{
    /**
     * @covers CacheBuilder::__construct
     * @covers CacheBuilder::setDrivers
     * @covers CacheBuilder::isAccessible
     */
    public function testSetDriversWithInvalidType()
    {
        $this->expectExceptionMessage('Driver list must be an accessible array');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'drivers' => false,
        ]);
    }

    /**
     * @covers CacheBuilder::setLogger
     */
    public function testSetLoggerWithInvalidType()
    {
        $this->expectExceptionMessage('Expected an instance of Psr\Log\LoggerInterface');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'logger' => new \stdClass(),
        ]);
    }

    /**
     * @covers CacheBuilder::__construct
     * @covers CacheBuilder::setNamespace
     */
    public function testSetNamespace()
    {
        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'namespace' => 'qux',
        ]);

        /** 1. Pool's Namespace */
        $pool = $builder($driver);
        $this->assertEquals('qux', $pool->getNamespace());

        /** 2. Overridden namespace */
        $pool = $builder($driver, 'foo');
        $this->assertEquals('foo', $pool->getNamespace());
    }

    /**
     * @covers CacheBuilder::setNamespace
     */
    public function testSetInvalidNamespace()
    {
        $this->expectExceptionMessage('Namespace must be alphanumeric');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'namespace' => '!@#$%^&*(',
        ]);
    }

    /**
     * @covers CacheBuilder::__construct
     * @covers CacheBuilder::setItemClass
     */
    public function testSetItemClass()
    {
        $itemClass = \Stash\Item::class;

        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'item_class' => $itemClass,
        ]);

        /** 1. Pool's Item Class */
        $pool = $builder($driver);
        $item = $pool->getItem('test');
        $this->assertInstanceOf($itemClass, $item);
    }

    /**
     *
     * @covers CacheBuilder::setItemClass
     */
    public function testSetFakeItemClass()
    {
        $this->expectExceptionMessage('Item class FakeClassName does not exist');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'item_class' => 'FakeClassName',
        ]);
    }

    /**
     *
     * @covers CacheBuilder::setItemClass
     */
    public function testSetInvalidItemClass()
    {
        $this->expectExceptionMessage('Item class stdClass must inherit from Stash\Interfaces\ItemInterface');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'item_class' => 'stdClass',
        ]);
    }

    /**
     * @covers CacheBuilder::__construct
     * @covers CacheBuilder::setPoolClass
     */
    public function testSetPoolClass()
    {
        $mockPool      = $this->createMock(PoolInterface::class);
        $mockClassName = get_class($mockPool);

        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'pool_class' => $mockClassName,
        ]);

        // Predefined pool class
        $pool = $builder($driver);
        $this->assertInstanceOf($mockClassName, $pool);
    }

    /**
     * @covers CacheBuilder::setPoolClass
     */
    public function testSetFakePoolClass()
    {
        $this->expectExceptionMessage('Pool class FakeClassName does not exist');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'pool_class' => 'FakeClassName',
        ]);
    }

    /**
     * @covers CacheBuilder::setPoolClass
     */
    public function testSetInvalidPoolClass()
    {
        $this->expectExceptionMessage('Pool class stdClass must inherit from Stash\Interfaces\PoolInterface');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'pool_class' => 'stdClass',
        ]);
    }
}
