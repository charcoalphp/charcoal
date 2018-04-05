<?php

namespace Charcoal\Tests\Cache\Factory;

use InvalidArgumentException;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash'
use Stash\DriverList;
use Stash\Interfaces\DriverInterface;
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;
use Stash\Pool;

// From 'charcoal-cache'
use Charcoal\Cache\CacheBuilder;

/**
 * Test CacheBuilder with default options.
 *
 * @coversDefaultClass \Charcoal\Cache\CacheBuilder
 */
class CacheBuilderTest extends AbstractCacheBuilderTest
{
    /**
     * @covers ::__construct
     */
    public function testDefaultOptions()
    {
        $builder = $this->builderFactory();

        $this->assertAttributeEquals(null, 'logger', $builder);
        $this->assertAttributeEquals(null, 'namespace', $builder);
        $this->assertAttributeEquals(Pool::class, 'poolClass', $builder);
        $this->assertAttributeEquals(null, 'itemClass', $builder);
    }

    /**
     * @covers ::__construct
     * @covers ::setDrivers
     */
    public function testSetDrivers()
    {
        /** 1. Accepts Primitive Array */
        $drivers = (array)$this->getDriverClassNames();
        $builder = $this->builderFactory([
            'drivers' => $drivers,
        ]);

        $this->assertAttributeInternalType('array', 'drivers', $builder);

        /** 2. Accepts Array Accessible Object */
        $drivers = new \ArrayObject($this->getDriverClassNames());
        $builder = $this->builderFactory([
            'drivers' => $drivers,
        ]);

        $this->assertAttributeInstanceOf(\ArrayAccess::class, 'drivers', $builder);

        /** 3. Rejects anything else */
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->builderFactory([
            'drivers' => false,
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::setLogger
     */
    public function testSetLogger()
    {
        $logger  = new NullLogger;
        $builder = $this->builderFactory([
            'logger' => $logger,
        ]);

        /** 1. Builder's Logger */
        $this->assertAttributeSame($logger, 'logger', $builder);

        /** 2. Pool's Logger */
        $pool = $builder('Ephemeral');
        $this->assertAttributeSame($logger, 'logger', $pool);
    }

    /**
     * @covers ::setLogger
     */
    public function testSetInvalidLogger()
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->builderFactory([
            'logger' => new \stdClass(),
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::setNamespace
     */
    public function testSetNamespace()
    {
        $builder = $this->builderFactory([
            'namespace' => 'qux',
        ]);

        /** 1. Builder's Namespace */
        $this->assertAttributeEquals('qux', 'namespace', $builder);

        /** 2. Pool's Namespace */
        $pool = $builder('Ephemeral');
        $this->assertEquals('qux', $pool->getNamespace());

        /** 3. Overridden namespace */
        $pool = $builder('Ephemeral', 'foo');
        $this->assertEquals('foo', $pool->getNamespace());
    }

    /**
     * @covers ::setNamespace
     */
    public function testSetInvalidNamespace()
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->builderFactory([
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

        $builder = $this->builderFactory([
            'item_class' => $mockClassName,
        ]);

        /** 1. Builder's Item Class */
        $this->assertAttributeEquals($mockClassName, 'itemClass', $builder);

        /** 1. Pool's Item Class */
        $pool = $builder('Ephemeral');
        $this->assertAttributeEquals($mockClassName, 'itemClass', $pool);
    }

    /**
     * @covers ::setItemClass
     */
    public function testSetFakeItemClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->builderFactory([
            'item_class' => 'FakeClassName',
        ]);
    }

    /**
     * @covers ::setItemClass
     */
    public function testSetInvalidItemClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->builderFactory([
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

        $builder = $this->builderFactory([
            'pool_class' => $mockClassName,
        ]);

        $this->assertAttributeEquals($mockClassName, 'poolClass', $builder);

        // Predefined pool class
        $pool = $builder('Ephemeral');
        $this->assertInstanceOf($mockClassName, $pool);
    }

    /**
     * @covers ::setPoolClass
     */
    public function testSetFakePoolClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->builderFactory([
            'pool_class' => 'FakeClassName',
        ]);
    }

    /**
     * @covers ::setPoolClass
     */
    public function testSetInvalidPoolClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->builderFactory([
            'pool_class' => 'stdClass',
        ]);
    }
}
