<?php

namespace Charcoal\Ui\ServiceProvider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Charcoal\Factory\GenericFactory as Factory;
use Charcoal\Ui\Dashboard\DashboardBuilder;
use Charcoal\Ui\Dashboard\DashboardInterface;
use Charcoal\Ui\Dashboard\GenericDashboard;

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
            return new Factory([
                'base_class'         => DashboardInterface::class,
                'default_class'      => GenericDashboard::class,
                'arguments'          => [
                    [
                        'container'      => $container,
                        'logger'         => $container['logger'],
                        'view'           => $container['view'],
                        'widget_builder' => $container['widget/builder'],
                        'layout_builder' => $container['layout/builder'],
                    ],
                ],
                'resolver_options'   => [
                    'suffix' => 'Dashboard',
                ],
            ]);
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
}
