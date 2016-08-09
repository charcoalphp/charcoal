<?php

namespace Charcoal\Tests\Ui\ServiceProvider;

use \Pimple\Container;

use \Charcoal\Ui\ServiceProvider\UiServiceProvider;

/**
 *
 */
class UiServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public $obj;
    public $container;

    public function setUp()
    {
        $this->obj = new UiServiceProvider();
        $this->container = new Container();
    }

    /**
     * Asserts that the `register()` method
     * - Registers all services on the container
     */
    public function testRegisterRegistersAllProviders()
    {
        $this->container->register($this->obj);

        // \Charcoal\Ui\ServiceProvider\DashboardServiceProvider
        $this->assertTrue(isset($this->container['dashboard/factory']));
        $this->assertTrue(isset($this->container['dashboard/builder']));

        // \Charcoal\Ui\ServiceProvider\FormServiceProvider
        $this->assertTrue(isset($this->container['form/factory']));
        $this->assertTrue(isset($this->container['form/builder']));
        $this->assertTrue(isset($this->container['form/group/factory']));
        $this->assertTrue(isset($this->container['form/input/factory']));
        $this->assertTrue(isset($this->container['form/input/builder']));

        // \Charcoal\Ui\ServiceProvider\LayoutServiceProvider
        $this->assertTrue(isset($this->container['layout/factory']));
        $this->assertTrue(isset($this->container['layout/builder']));

        // \Charcoal\Ui\ServiceProvider\MenuServiceProvider
        $this->assertTrue(isset($this->container['menu/factory']));
        $this->assertTrue(isset($this->container['menu/builder']));
        $this->assertTrue(isset($this->container['menu/item/factory']));
        $this->assertTrue(isset($this->container['menu/item/builder']));
    }
}
