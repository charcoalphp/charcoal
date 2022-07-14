<?php

namespace Charcoal\Ui\Dashboard;

use InvalidArgumentException;
// From 'charcoal-ui'
use Charcoal\Ui\AbstractUiItem;
use Charcoal\Ui\Dashboard\DashboardInterface;
use Charcoal\Ui\Dashboard\DashboardTrait;
use Charcoal\Ui\Layout\LayoutAwareTrait;

/**
 * A Basic Dashboard
 *
 * Abstract implementation of {@see \Charcoal\Ui\Dashboard\DashboardInterface}.
 */
abstract class AbstractDashboard extends AbstractUiItem implements
    DashboardInterface
{
    use DashboardTrait;
    use LayoutAwareTrait;

    /**
     * Return a new dashboard.
     *
     * @param array|\ArrayAccess $data The class dependencies.
     */
    public function __construct($data = null)
    {
        parent::__construct($data);

        $this->setWidgetBuilder($data['widget_builder']);

        /** Satisfies {@see \Charcoal\Ui\Layout\LayoutAwareInterface} */
        $this->setLayoutBuilder($data['layout_builder']);

        /** Satisfies {@see \Charcoal\View\ViewableInterface} */
        $this->setView($data['view']);
    }
}
