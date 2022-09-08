<?php

namespace Charcoal\Tests;

// From Pimple
use Pimple\Container;

// From 'charcoal-core/tests'
use Charcoal\Tests\CoreContainerProvider;

/**
 * Integrates Charcoal's service container into PHPUnit.
 *
 * Ensures Charcoal framework is set-up for each test.
 */
trait CoreContainerIntegrationTrait
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var CoreContainerProvider
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
     * @return CoreContainerProvider
     */
    protected function getContainerProvider()
    {
        if ($this->containerProvider === null) {
            $this->setupContainer();
        }

        return $this->containerProvider;
    }

    /**
     * @return void
     * @see    CoreContainerProvider
     */
    private function setupContainer()
    {
        $provider  = new CoreContainerProvider();
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
