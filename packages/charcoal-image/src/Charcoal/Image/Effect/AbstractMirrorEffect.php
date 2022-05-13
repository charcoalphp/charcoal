<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
 * Flip an image horizontally or vertically.
 */
abstract class AbstractMirrorEffect extends AbstractEffect
{
    /**
     * Axis can be "x" (flip) or "y" (flop)
     * @var string $axis
     */
    private $axis = 'y';

    /**
     * @param string $axis The mirror axis.
     * @throws InvalidArgumentException If the argument is not x or y.
     * @return AbstractMirrorEffect Chainable
     */
    public function setAxis($axis)
    {
        $allowedVals = ['x', 'y'];
        if (!is_string($axis) || !in_array($axis, $allowedVals)) {
            throw new InvalidArgumentException(
                'Axis must be "x" or "y"'
            );
        }
        $this->axis = $axis;
        return $this;
    }

    /**
     * @return string
     */
    public function axis()
    {
        return $this->axis;
    }
}
