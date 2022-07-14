<?php

namespace Charcoal\Ui\Dashboard;

use InvalidArgumentException;
// From 'charcoal-user'
use Charcoal\User\AuthAwareInterface;
// From 'charcoal-ui'
use Charcoal\Ui\PrioritizableInterface;
use Charcoal\Ui\UiItemInterface;

/**
 * Provides an implementation of {@see \Charcoal\Ui\Dashboard\DashboardInterface}.
 */
trait DashboardTrait
{
    /**
     * A colletion of widgets.
     *
     * @var UiItemInterface[]
     */
    private $widgets = [];

    /**
     * Store a widget builder instance.
     *
     * @var object
     */
    protected $widgetBuilder;

    /**
     * A callback applied to each widget output by {@see self::widgets()}.
     *
     * @var callable
     */
    private $widgetCallback;

    /**
     * Comparison function used by {@see uasort()}.
     *
     * @param  PrioritizableInterface $a Sortable entity A.
     * @param  PrioritizableInterface $b Sortable entity B.
     * @return integer Sorting value: -1 or 1.
     */
    abstract protected function sortItemsByPriority(
        PrioritizableInterface $a,
        PrioritizableInterface $b
    );

    /**
     * Set a widget builder.
     *
     * @param  object $builder The builder to create customized widget objects.
     * @throws InvalidArgumentException If the argument is not a widget builder.
     * @return DashboardInterface Chainable
     */
    protected function setWidgetBuilder($builder)
    {
        if (is_object($builder)) {
            $this->widgetBuilder = $builder;
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument must be a widget builder, %s given',
                    (is_object($builder) ? get_class($builder) : gettype($builder))
                )
            );
        }

        return $this;
    }

    /**
     * Set a callback to be applied to each widget output by {@see self::widgets()}.
     *
     * @param  callable|null $callable A callback to be applied to each widget
     *     or NULL to disable the callback.
     * @throws InvalidArgumentException If the argument is not callable or NULL.
     * @return DashboardInterface Chainable
     */
    public function setWidgetCallback($callable)
    {
        if ($callable === null) {
            $this->widgetCallback = null;

            return $this;
        }

        if (is_callable($callable)) {
            $this->widgetCallback = $callable;
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument must be callable or NULL, %s given',
                    (is_object($callable) ? get_class($callable) : gettype($callable))
                )
            );
        }

        return $this;
    }

    /**
     * Set the dashboard's widgets.
     *
     * @param array $widgets A collection of widgets.
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
     * Add a widget to the dashboard.
     *
     * If a widget with the same $widgetIdent already exists, it will be overridden.
     *
     * @param  string                $widgetIdent The widget identifier.
     * @param  UiItemInterface|array $widget      The widget object or structure.
     * @throws InvalidArgumentException If the widget is invalid.
     * @return DashboardInterface Chainable
     */
    public function addWidget($widgetIdent, $widget)
    {
        if (!is_string($widgetIdent)) {
            throw new InvalidArgumentException(
                'Widget identifier needs to be a string'
            );
        }

        if ($widget instanceof UiItemInterface) {
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
     * Retrieve the dashboard's widgets.
     *
     * @param callable $widgetCallback A callback applied to each widget.
     * @return UiItemInterface[]|Generator
     */
    public function widgets(callable $widgetCallback = null)
    {
        $widgets = $this->widgets;
        uasort($widgets, [ $this, 'sortItemsByPriority' ]);

        $widgetCallback = isset($widgetCallback) ? $widgetCallback : $this->widgetCallback;
        foreach ($widgets as $widget) {
            if (isset($widget['permissions']) && $this instanceof AuthAwareInterface) {
                $widget->setActive($this->hasPermissions($widget['permissions']));
            }

            if (!$widget->active()) {
                continue;
            }

            if ($widgetCallback) {
                $widgetCallback($widget);
            }
            $this->setDynamicTemplate('widget_template', $widget->template());
            yield $widget;
        }
    }

    /**
     * Determine if the dashboard has any widgets.
     *
     * @return boolean
     */
    public function hasWidgets()
    {
        return ($this->numWidgets() > 0);
    }

    /**
     * Count the number of widgets attached to the dashboard.
     *
     * @return integer
     */
    public function numWidgets()
    {
        return count($this->widgets);
    }
}
