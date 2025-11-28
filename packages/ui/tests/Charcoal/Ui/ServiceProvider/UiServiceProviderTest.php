<?php

namespace Charcoal\Tests\Ui\ServiceProvider;

use DI\Container;
// From 'charcoal-ui'
use Charcoal\Ui\ServiceProvider\UiServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class UiServiceProviderTest extends AbstractTestCase
{
    /**
     * @var UiServiceProvider
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
        $this->obj = new UiServiceProvider();
        $this->container = new Container();
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

        // \Charcoal\Ui\ServiceProvider\DashboardServiceProvider
        $this->assertTrue($this->container->has('dashboard/factory'));
        $this->assertTrue($this->container->has('dashboard/builder'));

        // \Charcoal\Ui\ServiceProvider\FormServiceProvider
        $this->assertTrue($this->container->has('form/factory'));
        $this->assertTrue($this->container->has('form/builder'));
        $this->assertTrue($this->container->has('form/group/factory'));
        $this->assertTrue($this->container->has('form/input/factory'));
        $this->assertTrue($this->container->has('form/input/builder'));

        // \Charcoal\Ui\ServiceProvider\LayoutServiceProvider
        $this->assertTrue($this->container->has('layout/factory'));
        $this->assertTrue($this->container->has('layout/builder'));

        // \Charcoal\Ui\ServiceProvider\MenuServiceProvider
        $this->assertTrue($this->container->has('menu/factory'));
        $this->assertTrue($this->container->has('menu/builder'));
        $this->assertTrue($this->container->has('menu/item/factory'));
        $this->assertTrue($this->container->has('menu/item/builder'));
    }
}
