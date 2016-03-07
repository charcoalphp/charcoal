<?php

namespace Charcoal\Ui\ServiceProvider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

use \Charcoal\Ui\Dashboard\DashboardBuilder;
use \Charcoal\Ui\Dashboard\DashboardFactory;

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
        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutFactory
         */
        $container['dashboard/factory'] = function (Container $container) {

            $dashboardFactory = new DashboardFactory();
            $dashboardFactory->setArguments([
                'widget_builder' => $container['widget/builder'],
                'layout_builder' => $container['layout/builder']
            ]);
            return $dashboardFactory;
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutBuilder
         */
        $container['dashboard/builder'] = function (Container $container) {
            $dashboardFactory = $container['dashboard/factory'];
            $dashboardBuilder = new DashboardBuilder($dashboardFactory, $container);
            return $dashboardBuilder;
        };
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    private function registerWidgetServices(Container $container)
    {

    }
}
