<?php

namespace Charcoal\Tests;

// From Pimple
use \Pimple\Container;

// From 'charcoal-core/tests'
use \Charcoal\Tests\ContainerProvider;

/**
 * Integrates Charcoal's service container into PHPUnit.
 *
 * Ensures Charcoal framework is set-up for each test.
 */
trait ContainerIntegrationTrait
{
    /**
     * @see    ContainerProvider
     * @return Container
     */
    private function getContainer()
    {
        $provider  = new ContainerProvider();
        $container = new Container();

        $provider->registerBaseServices($container);
        $provider->registerTranslator($container);
        $provider->registerMetadataLoader($container);
        $provider->registerSourceFactory($container);
        $provider->registerPropertyFactory($container);
        $provider->registerModelFactory($container);
        $provider->registerModelCollectionLoader($container);

        return $container;
    }
}
