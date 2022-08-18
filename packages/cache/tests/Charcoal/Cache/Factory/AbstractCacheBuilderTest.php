<?php

namespace Charcoal\Tests\Cache\Factory;

// From 'tedivm/stash'
use Stash\DriverList;
use Stash\Interfaces\DriverInterface;
use Stash\Interfaces\PoolInterface;
use Stash\Pool;

// From 'charcoal-cache'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Cache\CacheBuilder;

/**
 * Base CacheBuilder Test
 */
abstract class AbstractCacheBuilderTest extends AbstractTestCase
{
    /**
     * Create a new CacheBuilder instance.
     *
     * @param  array $args Parameters for the initialization of a CacheBuilder.
     * @return CacheBuilder
     */
    public function createBuilder(array $args = [])
    {
        if (!isset($args['drivers'])) {
            $args['drivers'] = $this->getDriverClassNames();
        }

        return new CacheBuilder($args);
    }

    /**
     * Create a new cache driver for a specific driver name.
     *
     * @param  string $name    Cache driver name.
     * @param  array  $options Cache driver options.
     * @return DriverInterface
     */
    public function createDriver($name, array $options = [])
    {
        $class = DriverList::getDriverClass($name);
        return new $class($options);
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
     * Create a new cache driver for a specific driver name.
     *
     * @param  string $name Cache driver name.
     * @return string
     */
    public function getDriverClass($name)
    {
        return DriverList::getDriverClass($name);
    }

    /**
     * Returns the default attributes of the CacheBuilder.
     *
     * @return array
     */
    public function getDefaultBuilderAttributes()
    {
        return [
            'pool_class' => Pool::class,
            'item_class' => null,
            'namespace'  => null,
            'logger'     => null,
        ];
    }

    /**
     * Returns the default attributes of the Stash Pool.
     *
     * @return array
     */
    public function getDefaultPoolAttributes()
    {
        return [
            'item_class' => '\Stash\Item',
            'namespace'  => null,
            'logger'     => null,
        ];
    }

    /**
     * Reports an error if $builder does not use the default options.
     *
     * @param  CacheBuilder $builder The cache builder to test.
     * @return void
     */
    public function assertCacheBuilderHasDefaultAttributes(CacheBuilder $builder)
    {
        $builderDefaults = $this->getDefaultBuilderAttributes();

        $this->assertAttributeEquals($builderDefaults['pool_class'], 'poolClass', $builder);
        $this->assertAttributeEquals($builderDefaults['item_class'], 'itemClass', $builder);
        $this->assertAttributeEquals($builderDefaults['namespace'], 'namespace', $builder);
        $this->assertAttributeEquals($builderDefaults['logger'], 'logger', $builder);
    }

    /**
     * Reports an error if $pool does not use the default options.
     *
     * @param  PoolInterface $pool The cache pool to test.
     * @return void
     */
    public function assertCachePoolHasDefaultAttributes(PoolInterface $pool)
    {
        $builderDefaults = $this->getDefaultBuilderAttributes();
        $poolDefaults    = $this->getDefaultPoolAttributes();

        $this->assertInstanceOf($builderDefaults['pool_class'], $pool);
        $this->assertEquals($poolDefaults['item_class'], $pool->getItemClass());
        $this->assertEquals($poolDefaults['namespace'], $pool->getNamespace());
        $this->assertEquals($poolDefaults['logger'], $pool->getLogger());
    }
}
