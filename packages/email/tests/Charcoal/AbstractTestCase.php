<?php

namespace Charcoal\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use DI\Container;

/**
 * Basic Charcoal Test
 */
abstract class AbstractTestCase extends BaseTestCase
{
    private Container $container;

    /**
     * Set up the service container.
     *
     * @return Container
     */
    protected function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerAdminServices($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
