<?php

namespace Charcoal\Admin\Widget;

use DI\Container;
// From 'charcoal-ui'
use Charcoal\Ui\Dashboard\DashboardInterface;
use Charcoal\Ui\Dashboard\DashboardTrait;
use Charcoal\Ui\Layout\LayoutAwareTrait;
use Charcoal\Ui\UiItemTrait;
// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Psr\Container\ContainerInterface;

/**
 * The dashboard widget is a simple dashboard interface / layout aware object.
 */
class DashboardWidget extends AdminWidget implements
    DashboardInterface
{
    use DashboardTrait;
    use LayoutAwareTrait;
    use UiItemTrait;

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function setDependencies(ContainerInterface $container)
    {
        parent::setDependencies($container);

        // Satisfies DashboardInterface dependencies
        $this->setWidgetBuilder($container->get('widget/builder'));

        // Satisfies LayoutAwareInterface dependencies
        $this->setLayoutBuilder($container->get('layout/builder'));
    }
}
