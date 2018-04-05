<?php

namespace Charcoal\Tests\Cache\Factory;

use InvalidArgumentException;

// From 'tedivm/stash'
use Stash\DriverList;
use Stash\Interfaces\DriverInterface;
use Stash\Pool;

// From 'charcoal-cache'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Cache\CacheBuilder;

/**
 * Test CacheBuilder
 *
 * @coversDefaultClass \Charcoal\Cache\CacheBuilder
 */
abstract class AbstractCacheBuilderTest extends AbstractTestCase
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
    public function testBuildFromArrayOfDriverClassNames()
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
    public function testBuildFromArrayOfDriverInstances()
    {
        $drivers = $this->getDriverInstances();
        $builder = $this->builderFactory([
            'drivers' => $drivers,
        ]);

        $pool = $builder('Ephemeral');
        $this->assertSame($drivers['Ephemeral'], $pool->getDriver());
    }

    /**
     * Test builder with an instance of {@see DriverInterface}.
     *
     * @covers ::build
     * @covers ::parsePoolOptions
     * @covers ::resolveDriver
     */
    public function testBuildWithDriverInstance()
    {
        $builder = $this->builderFactory();

        $mockDriver = $this->createMock(DriverInterface::class);

        $pool = $builder->build($mockDriver);
        $this->assertSame($mockDriver, $pool->getDriver());
    }

    /**
     * Test builder with a driver class name.
     *
     * @covers ::parsePoolOptions
     * @covers ::resolveDriver
     */
    public function testBuildWithDriverClassName()
    {
        $builder = $this->builderFactory();

        $mockDriver    = $this->createMock(DriverInterface::class);
        $mockClassName = get_class($mockDriver);

        $pool = $builder->build($mockClassName);
        $this->assertInstanceOf($mockClassName, $pool->getDriver());
    }

    /**
     * Test builder with an invalid instance of {@see DriverInterface}.
     *
     * @covers ::resolveDriver
     */
    public function testBuildWithInvalidDriverInstance()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->builderFactory();
        $builder->build(new \stdClass());
    }

    /**
     * Test builder with an invalid driver class name.
     *
     * @covers ::resolveDriver
     */
    public function testBuildWithInvalidDriverClassName()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->builderFactory();
        $builder->build('xyzzy');
    }

    /**
     * Returns a list of cache drivers that are also supported by this system.
     *
     * @return DriverInterface[] Driver Name => Object
     */
    public function getDriverInstances()
    {
        $drivers = $this->getDriverClassNames();
        foreach ($drivers as $name => $class) {
            $drivers[$name] = new $class;
        }
        return $drivers;
    }

    /**
     * Returns a list of cache drivers that are also supported by this system.
     *
     * @return string[] Driver Name => Class Name
     */
    public function getDriverClassNames()
    {
        $drivers = DriverList::getAvailableDrivers();
        unset($drivers['Composite']);
        return $drivers;
    }

    /**
     * Create a new CacheBuilder instance.
     *
     * @param  array $args Parameters for the initialization of a CacheBuilder.
     * @return CacheBuilder
     */
    public function builderFactory(array $args = [])
    {
        if (!isset($args['drivers'])) {
            $args['drivers'] = $this->getDriverClassNames();
        }

        return new CacheBuilder($args);
    }
}
