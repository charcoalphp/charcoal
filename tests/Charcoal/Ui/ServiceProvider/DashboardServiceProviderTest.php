<?php

namespace Charcoal\Tests\Ui\ServiceProvider;

// From PSR-3
use \Psr\Log\NullLogger;

// From Pimple
use \Pimple\Container;

// From 'charcoal-ui'
use \Charcoal\Ui\ServiceProvider\DashboardServiceProvider;

/**
 *
 */
class DashboardServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public $obj;
    public $container;

    public function setUp()
    {
        $this->obj = new DashboardServiceProvider();
        $this->container = new Container();

        $this->container['logger'] = function (Container $container) {
            return new NullLogger();
        };

        // Required depdendencies (stub)
        $this->container['widget/builder'] = function (Container $container) {
            return null;
        };
        $this->container['layout/builder'] = function (Container $container) {
            return null;
        };
    }

    /**
     * Asserts that the `register()` method
     * - Registers all services on the container
     */
    public function testRegisterRegistersAllProviders()
    {
        $this->container->register($this->obj);

        $this->assertTrue(isset($this->container['dashboard/factory']));
        $this->assertTrue(isset($this->container['dashboard/builder']));
    }

    public function testDashboardFactory()
    {
        $this->container->register($this->obj);
        $factory = $this->container['dashboard/factory'];
        $this->assertInstanceOf('\Charcoal\Factory\GenericFactory', $factory);
    }

    public function testDashboardBuilder()
    {
        $this->container->register($this->obj);
        $factory = $this->container['dashboard/builder'];
        $this->assertInstanceOf('\Charcoal\Ui\Dashboard\DashboardBuilder', $factory);
    }
}
