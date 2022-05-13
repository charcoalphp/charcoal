<?php

namespace Charcoal\Ui\Layout;

// From 'charcoal-ui'
use Charcoal\Ui\Layout\LayoutBuilder;

/**
 * Defines a layout-aware entity.
 *
 * Manages UI items through the layout, which is created with
 * a {@see LayoutBuilder}.
 */
interface LayoutAwareInterface
{
    /**
     * @param LayoutBuilder $builder The layout builder, to create customized layout object(s).
     * @return DashboardInterface Chainable
     */
    public function setLayoutBuilder(LayoutBuilder $builder);

    /**
     * @param LayoutInterface|array $layout The layout object or structure.
     * @return DashboardInterface Chainable
     */
    public function setLayout($layout);

    /**
     * @return LayoutInterface
     */
    public function layout();
}
