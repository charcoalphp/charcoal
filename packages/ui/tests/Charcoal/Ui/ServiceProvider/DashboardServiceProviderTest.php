<?php

namespace Charcoal\Tests\Ui\ServiceProvider;

// From PSR-3
use Psr\Log\NullLogger;
use DI\Container;
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

        $this->container->set('logger', function () {
            return new NullLogger();
        });

        // Required depdendencies (stub)
        $this->container->set('view', function () {
            return null;
        });
        $this->container->set('widget/builder', function () {
            return null;
        });
        $this->container->set('layout/builder', function () {
            return null;
        });
    }

    /**
     * Asserts that the `register()` method
     * - Registers all services on the container
     *
     * @return void
     */
    public function testRegisterRegistersAllProviders()
    {
        $this->obj->register($this->container);

        $this->assertTrue($this->container->has('dashboard/factory'));
        $this->assertTrue($this->container->has('dashboard/builder'));
    }

    /**
     * @return void
     */
    public function testDashboardFactory()
    {
        $this->obj->register($this->container);
        $factory = $this->container->get('dashboard/factory');
        $this->assertInstanceOf('\Charcoal\Factory\GenericFactory', $factory);
    }

    /**
     * @return void
     */
    public function testDashboardBuilder()
    {
        $this->obj->register($this->container);
        $factory = $this->container->get('dashboard/builder');
        $this->assertInstanceOf('\Charcoal\Ui\Dashboard\DashboardBuilder', $factory);
    }
}
