<?php

namespace Charcoal\Tests\Ui;

// From Pimple
use Pimple\Container;

// From 'charcoal-core/tests'
use Charcoal\Tests\Ui\ContainerProvider;

/**
 * Integrates Charcoal's service container into PHPUnit.
 *
 * Ensures Charcoal framework is set-up for each test.
 */
trait ContainerIntegrationTrait
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @see    ContainerProvider
     * @return Container
     */
    private function getContainer()
    {
        if ($this->container === null) {
            $provider  = new ContainerProvider();
            $container = new Container();

            $provider->registerBaseServices($container);
            $provider->registerAuthServices($container);
            $provider->registerTranslator($container);
            $provider->registerMetadataLoader($container);
            $provider->registerSourceFactory($container);
            $provider->registerPropertyFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
