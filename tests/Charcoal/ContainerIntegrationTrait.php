<?php

namespace Charcoal\Tests;

// From Pimple
use Pimple\Container;

// From 'charcoal-attachment'
use Charcoal\Tests\ContainerProvider;

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
        $provider->registerViewServiceProvider($container);
        $provider->registerModelServiceProvider($container);
        $provider->registerAuthServiceProvider($container);
        $provider->registerTranslatorServiceProvider($container);

        $this->container = $container;
        $this->containerProvider = $provider;
    }
}
