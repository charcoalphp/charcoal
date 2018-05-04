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
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Driver is empty
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnEmptyDriver()
    {
        $builder = $this->createBuilder();
        $builder->build('');
    }

    /**
     * Test builder with an invalid driver instance.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Driver class stdClass must implement Stash\Interfaces\DriverInterface
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnInvalidDriverInstance()
    {
        $builder = $this->createBuilder();
        $driver  = new StdClass();

        $pool = $builder->build($driver);
    }

    /**
     * Test builder with a named driver associated to an empty value.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Driver "foobar" does not exist
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnNamedDriverWithEmptyEntry()
    {
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
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Driver "foobar": Class stdClass must implement Stash\Interfaces\DriverInterface
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnNamedDriverWithBadEntry()
    {
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
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Driver "FakeClassName" cannot be resolved
     *
     * @covers ::resolveOneDriver
     */
    public function testBuildOnInvalidDriverClass()
    {
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
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Drivers cannot be resolved
     *
     * @covers ::resolveDriver
     */
    public function testBuildOnArrayOfInvalidDrivers()
    {
        $builder = $this->createBuilder();
        $builder->build([ 0 ]);
    }
}
