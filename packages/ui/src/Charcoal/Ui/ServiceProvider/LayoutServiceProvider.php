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
     */
    public function register(Container $container): void
    {
        $this->registerLayoutServices($container);
    }

    /**
     * @param Container $container A Pimple DI container.
     */
    private function registerLayoutServices(Container $container): void
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutFactory
         */
        $container['layout/factory'] = (fn(): \Charcoal\Ui\Layout\LayoutFactory => new LayoutFactory());

        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutBuilder
         */
        $container['layout/builder'] = function (Container $container): \Charcoal\Ui\Layout\LayoutBuilder {
            $layoutFactory = $container['layout/factory'];
            return new LayoutBuilder($layoutFactory, $container);
        };
    }
}
