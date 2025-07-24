<?php

namespace Charcoal\Ui\ServiceProvider;

use DI\Container;
// From 'charcoal-ui'
use Charcoal\Ui\Layout\LayoutBuilder;
use Charcoal\Ui\Layout\LayoutFactory;
use Psr\Container\ContainerInterface;

/**
 *
 */
class LayoutServiceProvider
{
    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function register(ContainerInterface $container)
    {
        $this->registerLayoutServices($container);
    }

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    private function registerLayoutServices(ContainerInterface $container)
    {
        /**
         * @param Container $container A DI Container.
         * @return LayoutFactory
         */
        $container->set('layout/factory', function () {
            $layoutFactory = new LayoutFactory();
            return $layoutFactory;
        });

        /**
         * @param Container $container A DI Container.
         * @return LayoutBuilder
         */
        $container->set('layout/builder', function (Container $container) {
            $layoutFactory = $container->get('layout/factory');
            $layoutBuilder = new LayoutBuilder($layoutFactory, $container);
            return $layoutBuilder;
        });
    }
}
