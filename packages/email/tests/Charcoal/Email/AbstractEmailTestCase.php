<?php

namespace Charcoal\Tests\Email;

use Charcoal\Tests\AbstractTestCase;
use DI\Container;
use Charcoal\Tests\Email\ContainerProvider;

/**
 * Basic Charcoal Test
 */
abstract class AbstractEmailTestCase extends AbstractTestCase
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
            $containerProvider->registerModelFactory($container);

            $container->set('email/factory', function (Container $container) {
                return $container->get('model/factory');
            });

            $this->container = $container;
        }

        return $this->container;
    }
}
