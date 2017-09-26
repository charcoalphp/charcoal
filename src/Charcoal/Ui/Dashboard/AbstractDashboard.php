<?php

namespace Charcoal\Ui\Dashboard;

use InvalidArgumentException;

// Intra-module (`charcoal-ui`) dependencies
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
        $this->setWidgetBuilder($data['widget_builder']);

        /** Satisfies {@see \Charcoal\Ui\Layout\LayoutAwareInterface} */
        $this->setLayoutBuilder($data['layout_builder']);

        parent::__construct($data);
    }
}
