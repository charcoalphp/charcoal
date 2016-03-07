<?php

namespace Charcoal\Ui\Dashboard;

use \InvalidArgumentException;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\AbstractUiItem;
use \Charcoal\Ui\Dashboard\DashboardInterface;
use \Charcoal\Ui\Dashboard\DashboardTrait;
use \Charcoal\Ui\Layout\LayoutAwareTrait;

/**
 * Full implementation of the Dashboard Interface, as abstract class.
 */
abstract class AbstractDashboard extends AbstractUiItem implements
    DashboardInterface
{
    use DashboardTrait;
    use LayoutAwareTrait;

    /**
     * @param array|ArrayAccess $data The class dependencies.
     */
    public function __construct($data = null)
    {
        $this->setWidgetBuilder($data['widget_builder']);

        // Set up layout builder (to fulfill LayoutAware Interface)
        $this->setLayoutBuilder($data['layout_builder']);
    }
}
