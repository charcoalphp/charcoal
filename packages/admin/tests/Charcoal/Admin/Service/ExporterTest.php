<?php

namespace Charcoal\Tests\Admin;

// From PSR-3
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory;

// From 'charcoal-admin'
use Charcoal\Admin\Service\Exporter;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class ExporterTest extends AbstractTestCase
{
    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    public function setUp(): void
    {
        $this->container();
    }

    public function testExport(): void
    {
        $this->assertTrue(true);
    }

    /**
     * Set up the service container.
     */
    protected function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerViewServiceProvider($container);
            $containerProvider->registerModelServiceProvider($container);
            $containerProvider->registerTranslatorServiceProvider($container);

            $container['view'] = $this->createMock(\Charcoal\View\ViewInterface::class);

            $this->container = $container;
        }

        return $this->container;
    }
}
