<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
 * Composite an opacity mask on top of the image
 */
abstract class AbstractMaskEffect extends AbstractEffect
{
    /**
     * The mask image source
     * @var string $mask
     */
    private $mask;

    /**
     * @var float $opacity
     */
    private $opacity = 1.0;

    /**
     * @var string $gravity
     */
    private $gravity = 'center';

    /**
     * Horizontal adjustment, in pixels.
     * Negative values will move mask to the left, positive values to the right.
     * Depends on the gravity setting
     * @var integer $x
     */
    private $x = 0;

    /**
     * Vertical adjustment, in pixels.
     * Negative values will move mask to the top, positive values to the bottom.
     * Depends on the gravity setting
     * @var integer $y
     */
    private $y = 0;

    /**
     * @param string $mask The mask image source.
     * @throws InvalidArgumentException If the mask source is not a string.
     * @return AbstractMaskEffect Chainable
     */
    public function setMask($mask)
    {
        if (!is_string($mask)) {
            throw new InvalidArgumentException(
                'Mask must be a string'
            );
        }
        $this->mask = $mask;
        return $this;
    }

    /**
     * @return string
     */
    public function mask()
    {
        return $this->mask;
    }

    /**
     * @param float $opacity The mask opacity.
     * @throws InvalidArgumentException If the mask opacity is not a numeric value or not between 0.0 and 1.0.
     * @return AbstractMaskEffect Chainable
     */
    public function setOpacity($opacity)
    {
        if (!is_numeric($opacity) || ($opacity < 0) || ( $opacity > 1)) {
            throw new InvalidArgumentException(
                'Opacity must be a float between 0.0 and 1.0'
            );
        }
        $this->opacity = (float)$opacity;
        return $this;
    }

    /**
     * @return float
     */
    public function opacity()
    {
        return $this->opacity;
    }

    /**
     * @param string $gravity The mask gravity.
     * @throws InvalidArgumentException If the argument is not a valid gravity name.
     * @return AbstractMaskEffect Chainable
     */
    public function setGravity($gravity)
    {
        if (!in_array($gravity, $this->image()->availableGravities())) {
            throw new InvalidArgumentException(
                'Gravity is not valid'
            );
        }
        $this->gravity = $gravity;
        return $this;
    }

    /**
     * @return string
     */
    public function gravity()
    {
        return $this->gravity;
    }

    /**
     * @param integer $x The mask X position.
     * @throws InvalidArgumentException If the position is not a numeric value.
     * @return AbstractMaskEffect Chainable
     */
    public function setX($x)
    {
        if (!is_numeric($x)) {
            throw new InvalidArgumentException(
                'X must be a an integer (in pixel)'
            );
        }
        $this->x = (int)$x;
        return $this;
    }

    /**
     * @return float
     */
    public function x()
    {
        return $this->x;
    }

    /**
     * @param integer $y The Y position.
     * @throws InvalidArgumentException If the position is not a numeric value.
     * @return AbstractMaskEffect Chainable
     */
    public function setY($y)
    {
        if (!is_numeric($y)) {
            throw new InvalidArgumentException(
                'Y must be a an integer (in pixel)'
            );
        }
        $this->y = (int)$y;
        return $this;
    }

    /**
     * @return float
     */
    public function y()
    {
        return $this->y;
    }
}
