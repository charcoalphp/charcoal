<?php

namespace Charcoal\Tests\Cache\Factory;

use InvalidArgumentException;

// From 'tedivm/stash'
use Stash\Interfaces\DriverInterface;
use Stash\Pool;

// From 'charcoal-cache'
use Charcoal\Cache\CacheBuilder;

/**
 * Test build features of the CacheBuilder.
 *
 * @coversDefaultClass \Charcoal\Cache\CacheBuilder
 */
class CacheBuilderDriverTest extends AbstractCacheBuilderTest
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
     * @covers ::resolveOneDriver
     */
    public function testConstructFromArrayOfDriverClassNames()
    {
        $drivers = $this->getDriverClassNames();
        $builder = $this->createBuilder([
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
     * @covers ::resolveOneDriver
     */
    public function testConstructFromArrayOfDriverInstances()
    {
        $drivers = $this->getDriverInstances();
        $builder = $this->createBuilder([
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
     * @covers ::resolveOneDriver
     */
    public function testBuildOnDriverClassName()
    {
        $builder = $this->createBuilder();

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
     * @covers ::isIterable
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnDriverInstance()
    {
        $builder = $this->createBuilder();

        $mockDriver = $this->createMock(DriverInterface::class);

        $pool = $builder->build($mockDriver);
        $this->assertSame($mockDriver, $pool->getDriver());
    }

    /**
     * Test builder with a collection of driver class names.
     *
     * @covers ::isIterable
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnArrayOfDriverClassNames()
    {
        $drivers = $this->getDriverInstances();
        $builder = $this->createBuilder([
            'drivers' => $drivers,
        ]);

        $pool = $builder->build([ 'Ephemeral', 'BlackHole' ]);
        $this->assertSame($drivers['Ephemeral'], $pool->getDriver());
    }

    /**
     * Test builder with a collection of instances of {@see DriverInterface}.
     *
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnArrayOfDriverInstances()
    {
        $drivers = $this->getDriverInstances();
        $builder = $this->createBuilder([
            'drivers' => $drivers,
        ]);

        $pool = $builder->build([ $drivers['Ephemeral'], $drivers['BlackHole'] ]);
        $this->assertSame($drivers['Ephemeral'], $pool->getDriver());
    }

    /**
     * Test builder with an invalid driver in a collection of drivers.
     *
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnArrayOfDriverClassNamesWithOneInvalidDriver()
    {
        $drivers = $this->getDriverInstances();
        $builder = $this->createBuilder([
            'drivers' => $drivers,
        ]);

        $pool = $builder->build([ 'Foobar', 'Ephemeral' ]);
        $this->assertSame($drivers['Ephemeral'], $pool->getDriver());
    }

    /**
     * Test builder with an invalid driver class name.
     *
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnInvalidDriverClassName()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $builder->build('xyzzy');
    }

    /**
     * Test builder with an invalid instance of {@see DriverInterface}.
     *
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnInvalidDriverInstance()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $builder->build(new \stdClass());
    }

    /**
     * Test builder with an empty array of drivers.
     *
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnEmptyArray()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $builder->build([ null ]);
    }

    /**
     * Test builder with an empty array of drivers.
     *
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnBadDriver()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $builder->build([ null ]);
    }
}
