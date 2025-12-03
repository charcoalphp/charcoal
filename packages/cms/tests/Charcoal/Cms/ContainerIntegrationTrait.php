<?php

namespace Charcoal\Tests\Cms;

use Charcoal\Tests\Cms\ContainerProvider;
use DI\Container;

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
     * Retrieve the model's mock dependencies.
     *
     * @return array
     */
    protected function getModelDependencies()
    {
        $container = $this->getContainer();

        return [
            'logger'           => $container->get('logger'),
            'property_factory' => $container->get('property/factory'),
            'metadata_loader'  => $container->get('metadata/loader'),
            'source_factory'   => $container->get('source/factory'),
        ];
    }

    /**
     * Retrieve the model's mock dependencies with the service locator.
     *
     * @return array
     */
    protected function getModelDependenciesWithContainer()
    {
        return ($this->getModelDependencies() + [
            'container' => $this->getContainer(),
        ]);
    }

    /**
     * Retrieve the property's mock dependencies with the service locator.
     *
     * @return array
     */
    protected function getPropertyDependenciesWithContainer()
    {
        $container = $this->getContainer();

        return [
            'container'  => $container,
            'logger'     => $container->get('logger'),
            'database'   => $container->get('database'),
            'translator' => $container->get('translator'),
        ];
    }

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
        $provider->registerModelDependencies($container);
        $provider->registerTemplateFactory($container);

        $this->container = $container;
        $this->containerProvider = $provider;
    }
}
