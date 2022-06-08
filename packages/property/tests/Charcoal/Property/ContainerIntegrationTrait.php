<?php

namespace Charcoal\Tests\Property;

// From Pimple
use Pimple\Container;

// From 'charcoal-property/tests'
use Charcoal\Tests\Property\ContainerProvider;

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
     * @var ContainerProvider
     */
    private $containerProvider;

    /**
     * @return Container
     */
    protected function getContainer()
    {
        if ($this->container === null) {
            $this->setupContainer();
        }

        return $this->container;
    }

    /**
     * @return ContainerProvider
     */
    protected function getContainerProvider()
    {
        if ($this->containerProvider === null) {
            $this->setupContainer();
        }

        return $this->containerProvider;
    }

    /**
     * @see    ContainerProvider
     * @return void
     */
    private function setupContainer()
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

        $this->container = $container;
        $this->containerProvider = $provider;
    }
}
