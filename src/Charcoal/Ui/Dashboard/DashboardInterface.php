<?php

namespace Charcoal\Ui\Dashboard;

// Intra-module (`charcoal-ui`) dependencies
use Charcoal\Ui\UiItemInterface;
use Charcoal\Ui\Layout\LayoutAwareInterface;

/**
 * Defines a dashboard.
 *
 * Dashboards are simply a collection of _widgets_, in a _layout_.
 *
 * - `layout` is a `LayoutInterface` object that can be created with a `LayoutBuilder`.
 * - `widgets` is a collection of any `UiItemInterface` objects.
 */
interface DashboardInterface extends UiItemInterface, LayoutAwareInterface
{
    /**
     * Set the dashboard's widgets.
     *
     * @param array $widgets A collection of widgets.
     * @return DashboardInterface Chainable
     */
    public function setWidgets(array $widgets);

    /**
     * Add a widget to the dashboard.
     *
     * @param  string                $widgetIdent The widget identifier.
     * @param  UiItemInterface|array $widget      The widget object or structure.
     * @return DashboardInterface Chainable
     */
    public function addWidget($widgetIdent, $widget);

    /**
     * Retrieve the dashboard's widgets.
     *
     * @return UiItemInterface[]|Generator
     */
    public function widgets();

    /**
     * Determine if the dashboard has any widgets.
     *
     * @return boolean
     */
    public function hasWidgets();

    /**
     * Count the number of widgets attached to the dashboard.
     *
     * @return integer
     */
    public function numWidgets();
}
