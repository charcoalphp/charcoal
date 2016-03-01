<?php

namespace Charcoal\Ui\Layout;

use \InvalidArgumentException;

use \Charcoal\Ui\Layout\LayoutBuilder;
use \Charcoal\Ui\Layout\LayoutInterface;

/**
* Full implementation, as trait, of the Layout Aware Interface.
*/
trait LayoutAwareTrait
{

    /**
     * @var LayoutInterface $layout
     */
    private $layout;

    /**
     * @var LayoutBuilder $layoutBuilder
     */
    protected $layoutBuilder = null;

    /**
     * @param LayoutBuilder $builder
     * @return DashboardInterface Chainable
     */
    public function setLayoutBuilder(LayoutBuilder $builder)
    {
        $this->layoutBuilder = $builder;
        return $this;
    }

    /**
     * @param LayoutInterface|array
     * @throws InvalidArgumentException
     * @return DashboardInterface Chainable
     */
    public function setLayout($layout)
    {
        if (($layout instanceof LayoutInterface)) {
            $this->layout = $layout;
        } elseif (is_array($layout)) {
            $l = $this->layoutBuilder->build($layout);
            $this->layout = $l;
        } else {
            throw new InvalidArgumentException(
                'Layout must be a LayoutInterface object or an array'
            );
        }
        return $this;
    }

    /**
     * @return LayoutInterface
     */
    public function layout()
    {
        return $this->layout;
    }
}
