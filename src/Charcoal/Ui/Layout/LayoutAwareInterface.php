<?php

namespace Charcoal\Ui\Layout;

use \Charcoal\Ui\Layout\LayoutBuilder;

/**
 * Layout-aware interface manage UI items through a layout, which is created with a LayoutBuilder
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
