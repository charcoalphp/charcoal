<?php

namespace Charcoal\Ui\ServiceProvider;

// From Pimple
use Pimple\Container;
use Pimple\ServiceProviderInterface;
// From 'charcoal-ui'
use Charcoal\Ui\Layout\LayoutBuilder;
use Charcoal\Ui\Layout\LayoutFactory;

/**
 *
 */
class LayoutServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerLayoutServices($container);
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    private function registerLayoutServices(Container $container)
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutFactory
         */
        $container['layout/factory'] = function () {

            $layoutFactory = new LayoutFactory();
            return $layoutFactory;
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutBuilder
         */
        $container['layout/builder'] = function (Container $container) {
            $layoutFactory = $container['layout/factory'];
            $layoutBuilder = new LayoutBuilder($layoutFactory, $container);
            return $layoutBuilder;
        };
    }
}
