<?php

namespace Charcoal\Tests\Cache\Factory;

use InvalidArgumentException;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash'
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;
use Stash\Pool;

/**
 * Test constructor and class attributes from the CacheBuilder.
 *
 * @coversDefaultClass \Charcoal\Cache\CacheBuilder
 */
class CacheBuilderClassTest extends AbstractCacheBuilderTest
{
    /**
     * @covers ::__construct
     * @covers ::setDrivers
     * @covers ::isAccessible
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
     * @covers ::setLogger
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
     * @covers ::__construct
     * @covers ::setNamespace
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
     * @covers ::setNamespace
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
     * @covers ::__construct
     * @covers ::setItemClass
     */
    public function testSetItemClass()
    {
        $mockItem      = $this->createMock(ItemInterface::class);
        $mockClassName = get_class($mockItem);

        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'item_class' => $mockClassName,
        ]);

        /** 1. Pool's Item Class */
        $pool = $builder($driver);
        $item = $pool->getItem('test');
        $this->assertInstanceOf($mockClassName, $item);
    }

    /**
     *
     * @covers ::setItemClass
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
     * @covers ::setItemClass
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
     * @covers ::__construct
     * @covers ::setPoolClass
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
     * @covers ::setPoolClass
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
     * @covers ::setPoolClass
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
