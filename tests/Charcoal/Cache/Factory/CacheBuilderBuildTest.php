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
 * Test build features of the CacheBuilder.
 *
 * @coversDefaultClass \Charcoal\Cache\CacheBuilder
 */
class CacheBuilderBuildTest extends AbstractCacheBuilderTest
{
    /**
     * Test builder with a map of driver names and class names.
     *
     * Note: This method also tests an invalid value for the $poolOptions parameter.
     *
     * @covers ::__invoke
     * @covers ::build
     * @covers ::parsePoolOptions
     * @covers ::resolveDriver
     */
    public function testConstructFromArrayOfDriverClassNames()
    {
        $drivers = $this->getDriverClassNames();
        $builder = $this->builderFactory([
            'drivers' => $drivers,
        ]);

        $pool = $builder('Ephemeral', false);
        $this->assertInstanceOf(Pool::class, $pool);
        $this->assertInstanceOf($drivers['Ephemeral'], $pool->getDriver());
    }

    /**
     * Test builder with a map of driver names and instances.
     *
     * @covers ::parsePoolOptions
     * @covers ::resolveDriver
     */
    public function testConstructFromArrayOfDriverInstances()
    {
        $drivers = $this->getDriverInstances();
        $builder = $this->builderFactory([
            'drivers' => $drivers,
        ]);

        $pool = $builder('Ephemeral');
        $this->assertSame($drivers['Ephemeral'], $pool->getDriver());
    }

    /**
     * Test builder with a driver class name.
     *
     * @covers ::parsePoolOptions
     * @covers ::resolveDriver
     */
    public function testBuildOnDriverClassName()
    {
        $builder = $this->builderFactory();

        $mockDriver    = $this->createMock(DriverInterface::class);
        $mockClassName = get_class($mockDriver);

        $pool = $builder->build($mockClassName);
        $this->assertInstanceOf($mockClassName, $pool->getDriver());
    }

    /**
     * Test builder with an instance of {@see DriverInterface}.
     *
     * @covers ::build
     * @covers ::parsePoolOptions
     * @covers ::resolveDriver
     */
    public function testBuildOnDriverInstance()
    {
        $builder = $this->builderFactory();

        $mockDriver = $this->createMock(DriverInterface::class);

        $pool = $builder->build($mockDriver);
        $this->assertSame($mockDriver, $pool->getDriver());
    }

    /**
     * Test builder with a collection of driver class names.
     *
     * @covers ::resolveDriver
     */
    public function testBuildOnArrayOfDriverClassNames()
    {
        $drivers = $this->getDriverInstances();
        $builder = $this->builderFactory([
            'drivers' => $drivers,
        ]);

        $pool = $builder->build([ 'Ephemeral', 'BlackHole' ]);
        $this->assertSame($drivers['Ephemeral'], $pool->getDriver());
    }

    /**
     * Test builder with a collection of instances of {@see DriverInterface}.
     *
     * @covers ::resolveDriver
     */
    public function testBuildOnArrayOfDriverInstances()
    {
        $drivers = $this->getDriverInstances();
        $builder = $this->builderFactory([
            'drivers' => $drivers,
        ]);

        $pool = $builder->build([ $drivers['Ephemeral'], $drivers['BlackHole'] ]);
        $this->assertSame($drivers['Ephemeral'], $pool->getDriver());
    }

    /**
     * Test builder with an invalid driver in a collection of drivers.
     *
     * @covers ::resolveDriver
     */
    public function testBuildOnArrayOfDriverClassNamesWithOneInvalidDriver()
    {
        $drivers = $this->getDriverInstances();
        $builder = $this->builderFactory([
            'drivers' => $drivers,
        ]);

        $pool = $builder->build([ 'Foobar', 'Ephemeral' ]);
        $this->assertSame($drivers['Ephemeral'], $pool->getDriver());
    }

    /**
     * Test builder with an invalid driver class name.
     *
     * @covers ::resolveDriver
     */
    public function testBuildOnInvalidDriverClassName()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->builderFactory();
        $builder->build('xyzzy');
    }

    /**
     * Test builder with an invalid instance of {@see DriverInterface}.
     *
     * @covers ::resolveDriver
     */
    public function testBuildOnInvalidDriverInstance()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->builderFactory();
        $builder->build(new \stdClass());
    }

    /**
     * Test builder with an invalid type in a collection of drivers.
     *
     * @covers ::resolveDriver
     */
    public function testBuildOnInvalidArrayInArray()
    {
        $this->expectException(InvalidArgumentException::class);

        $drivers = $this->getDriverInstances();
        $builder = $this->builderFactory([
            'drivers' => $drivers,
        ]);

        $builder->build([ [ 'Ephemeral' ], 'BlackHole' ]);
    }
}
