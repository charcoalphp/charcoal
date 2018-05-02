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
 * Base CacheBuilder Test
 */
abstract class AbstractCacheBuilderTest extends AbstractTestCase
{
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
