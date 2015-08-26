<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
* Composite a watermark on top of the image.
*/
abstract class AbstractWatermarkEffect extends AbstractEffect
{
    /**
    * The watermark image source
    * @var string $watermark
    */
    private $watermark;

    /**
    * @var float $opacity
    */
    private $opacity = 1.0;

    /**
    * The gravity
    * @var string $gravity
    */
    private $gravity = 'center';

    /**
    * Horizontal adjustment, in pixels.
    * Negative values will move watermark to the left, positive values to the right.
    * Depends on the gravity setting
    * @var integer $x
    */
    private $x = 0;

    /**
    * Vertical adjustment, in pixels.
    * Negative values will move watermark to the top, positive values to the bottom.
    * Depends on the gravity setting
    * @var integer $y
    */
    private $y = 0;

    /**
    * @param string $watermark
    * @throws InvalidArgumentException
    * @return AbstractMaskEffect Chainable
    */
    public function set_watermark($watermark)
    {
        if (!is_string($watermark)) {
            throw new InvalidArgumentException(
                'Mask must be a string'
            );
        }
        $this->watermark = $watermark;
        return $this;
    }

    /**
    * @return string
    */
    public function watermark()
    {
        return $this->watermark;
    }

    /**
    * @param float $opacity
    * @throws InvalidArgumentException
    * @return AbstractWatermarkEffect Chainable
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
    * @return AbstractWatermarkEffect Chainable
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
    * @return AbstractWatermarkEffect Chainable
    */
    public function set_x($x)
    {
        if (!is_int($x)) {
            throw new InvalidArgumentException(
                'X must be a an integer'
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
    * @return AbstractWatermarkEffect Chainable
    */
    public function set_y($y)
    {
        if (!is_int($y)) {
            throw new InvalidArgumentException(
                'Y must be a an integer'
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
