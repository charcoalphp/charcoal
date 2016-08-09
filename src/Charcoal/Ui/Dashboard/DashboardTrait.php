<?php

namespace Charcoal\Ui\Dashboard;

use \InvalidArgumentException;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\UiItemInterface;

/**
 * Full implementation of the Dashboard Interface, as abstract class.
 */
trait DashboardTrait
{
    /**
     * @var UiItemInterface[] $widgets
     */
    private $widgets = null;

    /**
     * @var mixed $widgetBuilder
     */
    protected $widgetBuilder = null;

    /**
     * @var callable $widgetCallback
     */
    private $widgetCallback = null;

    /**
     * @param mixed $builder The builder to create customized widget objects.
     * @return DashboardInterface Chainable
     */
    protected function setWidgetBuilder($builder)
    {
        $this->widgetBuilder = $builder;
        return $this;
    }

    /**
     * @param callable $cb The widget callback.
     * @return DashboardInterface Chainable
     */
    public function setWidgetCallback(callable $cb)
    {
        $this->widgetCallback = $cb;
        return $this;
    }

    /**
     * @param array $widgets The widgets.
     * @return DashboardInterface Chainable
     */
    public function setWidgets(array $widgets)
    {
        $this->widgets = [];
        foreach ($widgets as $widgetIdent => $widget) {
            $this->addWidget($widgetIdent, $widget);
        }
        return $this;
    }

    /**
     * @param string                $widgetIdent The widget identifier.
     * @param WidgetInterface|array $widget      The widget object or structure.
     * @throws InvalidArgumentException If the argument is not a widget object or structure.
     * @return DashboardInterface Chainable
     */
    public function addWidget($widgetIdent, $widget)
    {
        if (!is_string($widgetIdent)) {
            throw new InvalidArgumentException(
                'Widget ident needs to be a string'
            );
        }

        if (($widget instanceof UiItemInterface)) {
            $this->widgets[$widgetIdent] = $widget;
        } elseif (is_array($widget)) {
            if (!isset($widget['ident'])) {
                $widget['ident'] = $widgetIdent;
            }
            $w = $this->widgetBuilder->build($widget);
            $this->widgets[$widgetIdent] = $w;
        } else {
            throw new InvalidArgumentException(
                'Can not add widget: Invalid Widget.'
            );
        }
        return $this;
    }

    /**
     * Widgets generator.
     *
     * @param callable $widgetCallback Widget callback.
     * @return WidgetInterface[]|Generator
     */
    public function widgets(callable $widgetCallback = null)
    {
        $widgets = $this->widgets;
        uasort($widgets, [ $this, 'sortWidgetsByPriority' ]);

        $widgetCallback = isset($widgetCallback) ? $widgetCallback : $this->widgetCallback;
        foreach ($widgets as $widget) {
            if (!$widget->active()) {
                continue;
            }
            if ($widgetCallback) {
                $widgetCallback($widget);
            }
            $GLOBALS['widget_template'] = $widget->template();
            yield $widget;
        }
    }

    /**
     * @return boolean
     */
    public function hasWidgets()
    {
        return (count($this->widgets) > 0);
    }

    /**
     * @return integer
     */
    public function numWidgets()
    {
        return count($this->widgets);
    }

    /**
     * To be called with uasort()
     *
     * @param mixed $a Widget a.
     * @param mixed $b Widget b.
     * @return integer Sorting value: -1, 0, or 1
     */
    protected static function sortWidgetsByPriority($a, $b)
    {
        $a = $a->priority();
        $b = $b->priority();

        return ($a < $b) ? (-1) : 1;
    }
}
