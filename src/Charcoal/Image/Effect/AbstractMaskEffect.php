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
    * @param float $opacity
    */
    private $opacity = 1.0;

    /**
    * The gra
    */
    private $gravity = 'center';

    /**
    * Horizontal adjustment, in pixels.
    * Negative values will move mask to the left, positive values to the right.
    * Depends on the gravity setting
    * @param integer $x
    */
    private $x = 0;

    /**
    * Vertical adjustment, in pixels.
    * Negative values will move mask to the top, positive values to the bottom.
    * Depends on the gravity setting
    * @param integer $y
    */
    private $y = 0;

    /**
    * @param string $mask
    * @throws InvalidArgumentException
    * @return AbstractMaskEffect Chainable
    */
    public function set_mask($mask)
    {
        if (!is_string($mask)) {
            throw new InvalidArgumentException('Mask must be a string');
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
    * @param float $opacity
    * @throws InvalidArgumentException
    * @return AbstractMaskEffect Chainable
    */
    public function set_opacity($opacity)
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
    * @param string $gravity
    * @throws InvalidArgumentException
    * @return AbstractMaskEffect Chainable
    */
    public function set_gravity($gravity)
    {
        if (!in_array($gravity, $this->image()->available_gravities())) {
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
    * @param int $x
    * @throws InvalidArgumentException
    * @return AbstractMaskEffect Chainable
    */
    public function set_x($x)
    {
        if (!is_int($x)) {
            throw new InvalidArgumentException(
                'X must be a an integer (in pixel)'
            );
        }
        $this->x = $x;
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
    * @param int $y
    * @throws InvalidArgumentException
    * @return AbstractMaskEffect Chainable
    */
    public function set_y($y)
    {
        if (!is_int($y)) {
            throw new InvalidArgumentException(
                'Y must be a an integer (in pixel)'
            );
        }
        $this->y = $y;
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
