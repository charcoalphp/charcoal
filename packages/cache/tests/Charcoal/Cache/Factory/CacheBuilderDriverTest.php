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
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Cache\CacheBuilder::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'build')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'isIterable')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'resolveDriver')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'resolveOneDriver')]
class CacheBuilderDriverTest extends AbstractCacheBuilderTest
{
    /**
     * Test builder with a {@see DriverInterface driver object}.
     */
    public function testBuildOnDriverInstance(): void
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
    public function testBuildOnDriverClass(): void
    {
        $builder = $this->createBuilder();
        $driver  = $this->getDriverClass('BlackHole');

        $pool = $builder->build($driver);
        $this->assertInstanceOf($driver, $pool->getDriver());
    }

    /**
     * Test builder with a named driver associated to a {@see DriverInterface driver object}.
     */
    public function testBuildOnNamedDriverWithInstance(): void
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
    public function testBuildOnNamedDriverWithClass(): void
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
    public function testBuildOnEmptyDriver(): void
    {
        $this->expectExceptionMessage('Driver is empty');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $builder->build('');
    }

    /**
     * Test builder with an invalid driver instance.
     */
    public function testBuildOnInvalidDriverInstance(): void
    {
        $this->expectExceptionMessage('Driver class stdClass must implement Stash\Interfaces\DriverInterface');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $driver  = new StdClass();

        $builder->build($driver);
    }

    /**
     * Test builder with a named driver associated to an empty value.
     */
    public function testBuildOnNamedDriverWithEmptyEntry(): void
    {
        $this->expectExceptionMessage('Driver "foobar" does not exist');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder([
            'drivers' => [
                'foobar' => ''
            ]
        ]);

        $builder->build('foobar');
    }

    /**
     * Test builder with a named driver associated to an invalid instance.
     */
    public function testBuildOnNamedDriverWithBadEntry(): void
    {
        $this->expectExceptionMessage('Driver "foobar": Class stdClass must implement Stash\Interfaces\DriverInterface');
        $this->expectException(InvalidArgumentException::class);

        $driver  = new StdClass();
        $builder = $this->createBuilder([
            'drivers' => [
                'foobar' => $driver
            ]
        ]);

        $builder->build('foobar');
    }

    /**
     * Test builder with an invalid driver class.
     */
    public function testBuildOnInvalidDriverClass(): void
    {
        $this->expectExceptionMessage('Driver "FakeClassName" cannot be resolved');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $driver  = 'FakeClassName';

        $builder->build($driver);
    }



    // Resolve Many Drivers
    // =========================================================================
    /**
     * Test builder with an array of {@see DriverInterface driver objects}.
     */
    public function testBuildOnArrayOfDriverInstances(): void
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
    public function testBuildOnArrayOfInvalidDrivers(): void
    {
        $this->expectExceptionMessage('Drivers cannot be resolved');
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->createBuilder();
        $builder->build([ 0 ]);
    }
}
