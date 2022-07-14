<?php

namespace Charcoal\Ui\ServiceProvider;

// From Pimple
use Pimple\Container;
use Pimple\ServiceProviderInterface;
// From 'charcoal-user'
use Charcoal\User\ServiceProvider\AuthServiceProvider;
// From 'charcoal-ui'
use Charcoal\Ui\ServiceProvider\DashboardServiceProvider;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;

/**
 *
 */
class UiServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        $container->register(new AuthServiceProvider());
        $container->register(new DashboardServiceProvider());
        $container->register(new FormServiceProvider());
        $container->register(new LayoutServiceProvider());
        $container->register(new MenuServiceProvider());
    }
}
