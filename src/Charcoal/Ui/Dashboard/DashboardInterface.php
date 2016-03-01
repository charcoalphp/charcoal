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
     * @param LayoutInterface|array
     * @return DashboardInterface Chainable
     */
    public function setLayout($layout);

    /**
     * @return LayoutInterface
     */
    public function layout();

    /**
     * @param array $widgets
     * @return DashboardInterface Chainable
     */
    public function setWidgets(array $widgets);

    /**
     * @param string                $widget_ident
     * @param WidgetInterface|array $widget
     * @return DashboardInterface Chainable
     */
    public function addWidget($widgetIdent, $widget);

    /**
     * Widgets generator
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
