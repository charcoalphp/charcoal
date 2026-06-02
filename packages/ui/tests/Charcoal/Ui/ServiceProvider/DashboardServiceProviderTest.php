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

    protected function setUp(): void
    {
        $this->obj = new DashboardServiceProvider();
        $this->container = new Container();

        $this->container['logger'] = (fn(): \Psr\Log\NullLogger => new NullLogger());

        // Required depdendencies (stub)
        $this->container['view'] = (fn(): null => null);
        $this->container['widget/builder'] = (fn(): null => null);
        $this->container['layout/builder'] = (fn(): null => null);
    }

    /**
     * Asserts that the `register()` method
     * - Registers all services on the container
     */
    public function testRegisterRegistersAllProviders(): void
    {
        $this->container->register($this->obj);

        $this->assertTrue(isset($this->container['dashboard/factory']));
        $this->assertTrue(isset($this->container['dashboard/builder']));
    }

    public function testDashboardFactory(): void
    {
        $this->container->register($this->obj);
        $factory = $this->container['dashboard/factory'];
        $this->assertInstanceOf(\Charcoal\Factory\GenericFactory::class, $factory);
    }

    public function testDashboardBuilder(): void
    {
        $this->container->register($this->obj);
        $factory = $this->container['dashboard/builder'];
        $this->assertInstanceOf(\Charcoal\Ui\Dashboard\DashboardBuilder::class, $factory);
    }
}
