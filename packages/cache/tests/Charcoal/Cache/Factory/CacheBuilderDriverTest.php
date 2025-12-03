<?php

namespace Charcoal\Tests\Cache\Factory;

use StdClass;
use InvalidArgumentException;
// From 'charcoal-cache'
use Charcoal\Cache\CacheBuilder;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * Test the cache driver resolution from the CacheBuilder.
 */
#[CoversMethod(CacheBuilder::class, 'build')]
#[CoversMethod(CacheBuilder::class, 'isIterable')]
#[CoversMethod(CacheBuilder::class, 'resolveDriver')]
#[CoversMethod(CacheBuilder::class, 'resolveOneDriver')]
class CacheBuilderDriverTest extends AbstractCacheBuilderTestCase
{
    /**
     * Test builder with a {@see DriverInterface driver object}.
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
     */
    public function testBuildOnArrayOfInvalidDrivers()
    {
        $this->expectExceptionMessage('Drivers cannot be resolved');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $builder->build([ 0 ]);
    }
}
