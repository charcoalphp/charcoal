<?php

namespace Charcoal\Ui\ServiceProvider;

use DI\Container;
use Charcoal\Factory\GenericFactory as Factory;
use Charcoal\Ui\Dashboard\DashboardBuilder;
use Charcoal\Ui\Dashboard\DashboardInterface;
use Charcoal\Ui\Dashboard\GenericDashboard;
use Psr\Container\ContainerInterface;

/**
 *
 */
class DashboardServiceProvider
{
    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function register(ContainerInterface $container)
    {
        $this->registerDashboardServices($container);
    }

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    private function registerDashboardServices(ContainerInterface $container)
    {
        /**
         * @param Container $container A DI Container.
         * @return LayoutFactory
         */
        $container->set('dashboard/factory', function (Container $container) {
            return new Factory([
                'base_class'         => DashboardInterface::class,
                'default_class'      => GenericDashboard::class,
                'arguments'          => [
                    [
                        'container'      => $container,
                        'logger'         => $container->get('logger'),
                        'view'           => $container->get('view'),
                        'widget_builder' => $container->get('widget/builder'),
                        'layout_builder' => $container->get('layout/builder'),
                    ],
                ],
                'resolver_options'   => [
                    'suffix' => 'Dashboard',
                ],
            ]);
        });

        /**
         * @param Container $container A DI Container.
         * @return LayoutBuilder
         */
        $container->set('dashboard/builder', function (Container $container) {
            $dashboardFactory = $container->get('dashboard/factory');
            $dashboardBuilder = new DashboardBuilder($dashboardFactory, $container);
            return $dashboardBuilder;
        });
    }
}
