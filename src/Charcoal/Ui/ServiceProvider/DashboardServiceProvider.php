<?php

namespace Charcoal\Ui\ServiceProvider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

use \Charcoal\Ui\Layout\LayoutBuilder;
use \Charcoal\Ui\Layout\LayoutFactory;

/**
 *
 */
class DashboardServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerDashboardServices($container);
        $this->registerWidgetServices($container);
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    private function registerDashboardServices(Container $container)
    {

    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    private function registerWidgetServices(Container $container)
    {

    }
}
