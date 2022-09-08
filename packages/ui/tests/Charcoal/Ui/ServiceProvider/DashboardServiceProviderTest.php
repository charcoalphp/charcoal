<?php

namespace Charcoal\Tests\Ui\ServiceProvider;

// From PSR-3
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-ui'
use Charcoal\Ui\ServiceProvider\DashboardServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class DashboardServiceProviderTest extends AbstractTestCase
{
    /**
     * @var DashboardServiceProvider
     */
    public $obj;

    /**
     * @var Container
     */
    public $container;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = new DashboardServiceProvider();
        $this->container = new Container();

        $this->container['logger'] = function () {
            return new NullLogger();
        };

        // Required depdendencies (stub)
        $this->container['view'] = function () {
            return null;
        };
        $this->container['widget/builder'] = function () {
            return null;
        };
        $this->container['layout/builder'] = function () {
            return null;
        };
    }

    /**
     * Asserts that the `register()` method
     * - Registers all services on the container
     *
     * @return void
     */
    public function testRegisterRegistersAllProviders()
    {
        $this->container->register($this->obj);

        $this->assertTrue(isset($this->container['dashboard/factory']));
        $this->assertTrue(isset($this->container['dashboard/builder']));
    }

    /**
     * @return void
     */
    public function testDashboardFactory()
    {
        $this->container->register($this->obj);
        $factory = $this->container['dashboard/factory'];
        $this->assertInstanceOf('\Charcoal\Factory\GenericFactory', $factory);
    }

    /**
     * @return void
     */
    public function testDashboardBuilder()
    {
        $this->container->register($this->obj);
        $factory = $this->container['dashboard/builder'];
        $this->assertInstanceOf('\Charcoal\Ui\Dashboard\DashboardBuilder', $factory);
    }
}
