<?php

namespace Charcoal\Ui\Layout;

use \Charcoal\Ui\Layout\LayoutBuilder;

/**
 * Layout-aware interface manage UI items through a layout, which is created with a LayoutBuilder
 */
interface LayoutAwareInterface
{
    /**
     * @param LayoutBuilder $builder
     * @return DashboardInterface Chainable
     */
    public function setLayoutBuilder(LayoutBuilder $builder);
    /**
     * @param LayoutInterface|array
     * @throws InvalidArgumentException
     * @return DashboardInterface Chainable
     */
    public function setLayout($layout);

    /**
     * @return LayoutInterface
     */
    public function layout();
}
