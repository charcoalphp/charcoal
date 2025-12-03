<?php

namespace Charcoal\Tests\Cms;

use Charcoal\Tests\AbstractTestCase;
use DI\Container;
use Charcoal\Tests\Cms\ContainerProvider;

abstract class AbstractCmsTestCase extends AbstractTestCase
{
    private Container $container;

    /**
     * Set up the service container.
     *
     * @return Container
     */
    protected function container()
    {
        if (!isset($this->container)) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            //$containerProvider->registerModelDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
