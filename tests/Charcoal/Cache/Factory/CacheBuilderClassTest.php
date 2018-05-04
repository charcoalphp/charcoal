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
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Driver list must be an accessible array
     *
     * @covers ::__construct
     * @covers ::setDrivers
     * @covers ::isAccessible
     */
    public function testSetDriversWithInvalidType()
    {
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
        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'logger' => $logger,
        ]);

        /** 1. Builder's Logger */
        $this->assertAttributeSame($logger, 'logger', $builder);

        /** 2. Pool's Logger */
        $pool = $builder($driver);
        $this->assertAttributeSame($logger, 'logger', $pool);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Expected an instance of Psr\Log\LoggerInterface
     *
     * @covers ::setLogger
     */
    public function testSetLoggerWithInvalidType()
    {
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

        /** 1. Builder's Namespace */
        $this->assertAttributeEquals('qux', 'namespace', $builder);

        /** 2. Pool's Namespace */
        $pool = $builder($driver);
        $this->assertEquals('qux', $pool->getNamespace());

        /** 3. Overridden namespace */
        $pool = $builder($driver, 'foo');
        $this->assertEquals('foo', $pool->getNamespace());
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Namespace must be alphanumeric
     *
     * @covers ::setNamespace
     */
    public function testSetInvalidNamespace()
    {
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

        /** 1. Builder's Item Class */
        $this->assertAttributeEquals($mockClassName, 'itemClass', $builder);

        /** 1. Pool's Item Class */
        $pool = $builder($driver);
        $this->assertAttributeEquals($mockClassName, 'itemClass', $pool);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Item class FakeClassName does not exist
     *
     * @covers ::setItemClass
     */
    public function testSetFakeItemClass()
    {
        $builder = $this->createBuilder([
            'item_class' => 'FakeClassName',
        ]);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Item class stdClass must inherit from Stash\Interfaces\ItemInterface
     *
     * @covers ::setItemClass
     */
    public function testSetInvalidItemClass()
    {
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

        $this->assertAttributeEquals($mockClassName, 'poolClass', $builder);

        // Predefined pool class
        $pool = $builder($driver);
        $this->assertInstanceOf($mockClassName, $pool);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Pool class FakeClassName does not exist
     *
     * @covers ::setPoolClass
     */
    public function testSetFakePoolClass()
    {
        $builder = $this->createBuilder([
            'pool_class' => 'FakeClassName',
        ]);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Pool class stdClass must inherit from Stash\Interfaces\PoolInterface
     *
     * @covers ::setPoolClass
     */
    public function testSetInvalidPoolClass()
    {
        $builder = $this->createBuilder([
            'pool_class' => 'stdClass',
        ]);
    }
}
