<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Composite a watermark on top of the image.
*/
abstract class AbstractWatermarkEffect extends AbstractEffect
{
    /**
    * The watermark image source
    * @var string $_watermark
    */
    private $_watermark;

    /**
    * @param float $_opacity
    */
    private $_opacity = 1.0;

    /**
    * The gra
    */
    private $_gravity = 'center';
    /**
    * Horizontal adjustment, in pixels.
    * Negative values will move watermark to the left, positive values to the right.
    * Depends on the gravity setting
    * @param integer $_x
    */
    private $_x = 0;
    /**
    * Vertical adjustment, in pixels.
    * Negative values will move watermark to the top, positive values to the bottom.
    * Depends on the gravity setting
    * @param integer $_y
    */
    private $_y = 0;

    /**
    * @param array $data
    * @return AbstractTintEffect Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['watermark']) && $data['watermark'] !== null) {
            $this->set_watermark($data['watermark']);
        }
        if (isset($data['opacity']) && $data['opacity'] !== null) {
            $this->set_opacity($data['opacity']);
        }
        if (isset($data['gravity']) && $data['gravity'] !== null) {
            $this->set_gravity($data['gravity']);
        }
        if (isset($data['x']) && $data['x'] !== null) {
            $this->set_x($data['x']);
        }
        if (isset($data['y']) && $data['y'] !== null) {
            $this->set_y($data['y']);
        }

        return $this;
    }

    /**
    * @param string $watermark
    * @throws InvalidArgumentException
    * @return AbstractMaskEffect Chainable
    */
    public function set_watermark($watermark)
    {
        if (!is_string($watermark)) {
            throw new InvalidArgumentException('Mask must be a string');
        }
        $this->_watermark = $watermark;
        return $this;
    }

    /**
    * @return string
    */
    public function watermark()
    {
        return $this->_watermark;
    }

    /**
    * @param float $opacity
    * @throws InvalidArgumentException
    * @return AbstractWatermarkEffect Chainable
    */
    public function set_opacity($opacity)
    {
        if (!is_numeric($opacity) || ($opacity < 0) || ( $opacity > 1)) {
            throw new InvalidArgumentException('Opacity must be a float between 0.0 and 1.0');
        }
        $this->_opacity = (float)$opacity;
        return $this;
    }

    /**
    * @return float
    */
    public function opacity()
    {
        return $this->_opacity;
    }

    /**
    * @param string $gravity
    * @throws InvalidArgumentException
    * @return AbstractWatermarkEffect Chainable
    */
    public function set_gravity($gravity)
    {
        if (!in_array($gravity, $this->image()->available_gravities())) {
            throw new InvalidArgumentException('Gravity is not valid');
        }
        $this->_gravity = $gravity;
        return $this;
    }

    /**
    * @return string
    */
    public function gravity()
    {
        return $this->_gravity;
    }
    
    /**
    * @param int $x
    * @throws InvalidArgumentException
    * @return AbstractWatermarkEffect Chainable
    */
    public function set_x($x)
    {
        if (!is_int($x)) {
            throw new InvalidArgumentException('X must be a an integer');
        }
        $this->_x = $x;
        return $this;
    }

    /**
    * @return float
    */
    public function x()
    {
        return $this->_x;
    }

    /**
    * @param int $y
    * @throws InvalidArgumentException
    * @return AbstractWatermarkEffect Chainable
    */
    public function set_y($y)
    {
        if (!is_int($y)) {
            throw new InvalidArgumentException('Y must be a an integer');
        }
        $this->_y = $y;
        return $this;
    }

    /**
    * @return float
    */
    public function y()
    {
        return $this->_y;
    }

}
