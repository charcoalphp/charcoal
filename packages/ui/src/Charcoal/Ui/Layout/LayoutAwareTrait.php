<?php

namespace Charcoal\Ui\Layout;

use InvalidArgumentException;
// From 'charcoal-ui'
use Charcoal\Ui\Layout\LayoutBuilder;
use Charcoal\Ui\Layout\LayoutInterface;

/**
 * Provides an implementation of {@see \Charcoal\Ui\Layout\LayoutAwareInterface}.
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
    protected $layoutBuilder;

    /**
     * @param LayoutBuilder $builder The layout builder, to create customized layout object(s).
     * @return DashboardInterface Chainable
     */
    public function setLayoutBuilder(LayoutBuilder $builder)
    {
        $this->layoutBuilder = $builder;
        return $this;
    }

    /**
     * @param LayoutInterface|array $layout The layout object or structure.
     * @throws InvalidArgumentException If the layout argument is not an object or layout structure.
     * @return DashboardInterface Chainable
     */
    public function setLayout($layout)
    {
        if (($layout instanceof LayoutInterface)) {
            $this->layout = $layout;
        } elseif (is_array($layout)) {
            $this->layout = $this->layoutBuilder->build($layout);
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
