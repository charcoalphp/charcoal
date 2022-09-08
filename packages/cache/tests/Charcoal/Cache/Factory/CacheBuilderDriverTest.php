<?php

namespace Charcoal\Tests\Cache\Factory;

use StdClass;
use InvalidArgumentException;

// From 'tedivm/stash'
use Stash\Interfaces\DriverInterface;

// From 'charcoal-cache'
use Charcoal\Cache\CacheBuilder;

/**
 * Test the cache driver resolution from the CacheBuilder.
 *
 * @coversDefaultClass \Charcoal\Cache\CacheBuilder
 */
class CacheBuilderDriverTest extends AbstractCacheBuilderTest
{
    /**
     * Test builder with a {@see DriverInterface driver object}.
     *
     * @covers ::build
     */
    public function testBuildOnDriverInstance()
    {
        $builder = $this->createBuilder();
        $driver  = $this->createDriver('BlackHole');

        $pool = $builder->build($driver);
        $this->assertSame($driver, $pool->getDriver());
    }



    // Resolve One Driver
    // =========================================================================

    /**
     * Test builder with a driver class.
     *
     * @covers ::build
     * @covers ::isIterable
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnDriverClass()
    {
        $builder = $this->createBuilder();
        $driver  = $this->getDriverClass('BlackHole');

        $pool = $builder->build($driver);
        $this->assertInstanceOf($driver, $pool->getDriver());
    }

    /**
     * Test builder with a named driver associated to a {@see DriverInterface driver object}.
     *
     * @covers ::build
     * @covers ::isIterable
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnNamedDriverWithInstance()
    {
        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'drivers' => [
                'noop' => $driver
            ]
        ]);

        $pool = $builder->build('noop');
        $this->assertSame($driver, $pool->getDriver());
    }

    /**
     * Test builder with a named driver associated to a driver class.
     *
     * @covers ::build
     * @covers ::isIterable
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnNamedDriverWithClass()
    {
        $driver  = $this->getDriverClass('BlackHole');
        $builder = $this->createBuilder([
            'drivers' => [
                'noop' => $driver
            ]
        ]);

        $pool = $builder->build('noop');
        $this->assertInstanceOf($driver, $pool->getDriver());
    }

    // =================================

    /**
     * Test builder with an empty driver name.
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnEmptyDriver()
    {
        $this->expectExceptionMessage('Driver is empty');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $builder->build('');
    }

    /**
     * Test builder with an invalid driver instance.
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnInvalidDriverInstance()
    {
        $this->expectExceptionMessage('Driver class stdClass must implement Stash\Interfaces\DriverInterface');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $driver  = new StdClass();

        $pool = $builder->build($driver);
    }

    /**
     * Test builder with a named driver associated to an empty value.
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnNamedDriverWithEmptyEntry()
    {
        $this->expectExceptionMessage('Driver "foobar" does not exist');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder([
            'drivers' => [
                'foobar' => ''
            ]
        ]);

        $pool = $builder->build('foobar');
    }

    /**
     * Test builder with a named driver associated to an invalid instance.
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnNamedDriverWithBadEntry()
    {
        $this->expectExceptionMessage('Driver "foobar": Class stdClass must implement Stash\Interfaces\DriverInterface');
        $this->expectException(InvalidArgumentException::class);

        $driver  = new StdClass();
        $builder = $this->createBuilder([
            'drivers' => [
                'foobar' => $driver
            ]
        ]);

        $pool = $builder->build('foobar');
    }

    /**
     * Test builder with an invalid driver class.
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnInvalidDriverClass()
    {
        $this->expectExceptionMessage('Driver "FakeClassName" cannot be resolved');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $driver  = 'FakeClassName';

        $pool = $builder->build($driver);
    }



    // Resolve Many Drivers
    // =========================================================================

    /**
     * Test builder with an array of {@see DriverInterface driver objects}.
     *
     * @covers ::build
     * @covers ::isIterable
     * @covers ::resolveDriver
     * @covers ::resolveOneDriver
     */
    public function testBuildOnArrayOfDriverInstances()
    {
        $builder = $this->createBuilder();
        $driver  = $this->createDriver('BlackHole');

        $pool = $builder->build([ $driver ]);
        $this->assertSame($driver, $pool->getDriver());
    }

    // =================================

    /**
     * Test builder with an invalid array of drivers.
     *
     * @covers ::resolveDriver
     */
    public function testBuildOnArrayOfInvalidDrivers()
    {
        $this->expectExceptionMessage('Drivers cannot be resolved');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $builder->build([ 0 ]);
    }
}
