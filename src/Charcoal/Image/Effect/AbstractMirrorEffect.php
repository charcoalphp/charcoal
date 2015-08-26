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
    * @param string $axis
    * @throws InvalidArgumentException
    * @return AbstractMirrorEffect Chainable
    */
    public function set_axis($axis)
    {
        $allowed_vals = ['x', 'y'];
        if (!is_string($axis) || !in_array($axis, $allowed_vals)) {
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
