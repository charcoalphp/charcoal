<?php

namespace Charcoal\Tests\Admin\Template;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Template\ElfinderTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class ElfinderTemplateTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Admin\Template\ElfinderTemplate $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new ElfinderTemplate([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function testAdminAssertsUrl(): void
    {
        $ret = $this->obj->adminAssetsUrl();
        $this->assertEquals('/assets/admin/', $ret);
    }

    /**
     * Set up the service container.
     */
    protected function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerTemplateDependencies($container);
            $containerProvider->registerElfinderConfig($container);
            $container['widget/factory'] = $this->createMock(\Charcoal\Factory\FactoryInterface::class);

            $this->container = $container;
        }

        return $this->container;
    }
}
