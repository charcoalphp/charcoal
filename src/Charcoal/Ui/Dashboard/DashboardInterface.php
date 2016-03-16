<?php

namespace Charcoal\Ui\Dashboard;

use \Charcoal\Ui\UiItemInterface;
use \Charcoal\Ui\Layout\LayoutAwareInterface;

/**
 * Dashboard Interface.
 *
 * Dashboards are simply a collection of _widgets_, in a _layout_.
 *
 * - `layout` is a `LayoutInterface` object that can be created with a `LayoutBuilder`.
 * - `widgets` is a collection of any `UiItemInterface` objects.
 */
interface DashboardInterface extends UiItemInterface, LayoutAwareInterface
{
    /**
     * @param array $widgets The widgets.
     * @return DashboardInterface Chainable
     */
    public function setWidgets(array $widgets);

    /**
     * @param string                $widgetIdent The widget identifier.
     * @param WidgetInterface|array $widget      The widget object or structure.
     * @return DashboardInterface Chainable
     */
    public function addWidget($widgetIdent, $widget);

    /**
     * Widgets generator
     *
     * @return void This method is a `WidgetInterface` generator.
     */
    public function widgets();

    /**
     * @return boolean
     */
    public function hasWidgets();

    /**
     * @return integer
     */
    public function numWidgets();
}
