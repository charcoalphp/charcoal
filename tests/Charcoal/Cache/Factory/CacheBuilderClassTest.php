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
     */
    public function testDefaultOptions()
    {
        $builder = $this->createBuilder();

        $this->assertAttributeEquals(null, 'logger', $builder);
        $this->assertAttributeEquals(null, 'namespace', $builder);
        $this->assertAttributeEquals(Pool::class, 'poolClass', $builder);
        $this->assertAttributeEquals(null, 'itemClass', $builder);
    }

    /**
     * @covers ::__construct
     * @covers ::setDrivers
     * @covers ::isAccessible
     */
    public function testSetDrivers()
    {
        /** 1. Accepts Primitive Array */
        $drivers = (array)$this->getDriverClassNames();
        $builder = $this->createBuilder([
            'drivers' => $drivers,
        ]);

        $this->assertAttributeInternalType('array', 'drivers', $builder);

        /** 2. Accepts Array Accessible Object */
        $drivers = new \ArrayObject($this->getDriverClassNames());
        $builder = $this->createBuilder([
            'drivers' => $drivers,
        ]);

        $this->assertAttributeInstanceOf(\ArrayAccess::class, 'drivers', $builder);

        /** 3. Rejects anything else */
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
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
        $builder = $this->createBuilder([
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
        $builder = $this->createBuilder([
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

        $builder = $this->createBuilder([
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
        $builder = $this->createBuilder([
            'item_class' => 'FakeClassName',
        ]);
    }

    /**
     * @covers ::setItemClass
     */
    public function testSetInvalidItemClass()
    {
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

        $builder = $this->createBuilder([
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
        $builder = $this->createBuilder([
            'pool_class' => 'FakeClassName',
        ]);
    }

    /**
     * @covers ::setPoolClass
     */
    public function testSetInvalidPoolClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'pool_class' => 'stdClass',
        ]);
    }
}
