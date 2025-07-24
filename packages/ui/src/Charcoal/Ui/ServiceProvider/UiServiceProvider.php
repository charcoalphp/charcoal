<?php

namespace Charcoal\Ui\ServiceProvider;

use DI\Container;
// From 'charcoal-user'
use Charcoal\User\ServiceProvider\AuthServiceProvider;
// From 'charcoal-ui'
use Charcoal\Ui\ServiceProvider\DashboardServiceProvider;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;
use Psr\Container\ContainerInterface;

/**
 *
 */
class UiServiceProvider
{
    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function register(ContainerInterface $container)
    {
        (new AuthServiceProvider())->register($container);
        (new DashboardServiceProvider())->register($container);
        (new FormServiceProvider())->register($container);
        (new LayoutServiceProvider())->register($container);
        (new MenuServiceProvider())->register($container);
    }
}
