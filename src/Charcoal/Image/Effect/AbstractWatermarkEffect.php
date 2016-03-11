<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;
use \Charcoal\Image\ImageInterface;

/**
 * Composite a watermark on top of the image.
 */
abstract class AbstractWatermarkEffect extends AbstractEffect
{
    /**
     * The watermark image source (path or Image).
     * @var string|ImageInterface $watermark
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
    private $gravity = 'nw';

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
     * @param string|ImageInterface $watermark The watermark (path or Image).
     * @throws InvalidArgumentException If the watermark value is not a string or an Image.
     * @return AbstractMaskEffect Chainable
     */
    public function setWatermark($watermark)
    {
        if (is_string($watermark) || ($watermark instanceof ImageInterface)) {
            $this->watermark = $watermark;
            return $this;
        } else {
            throw new InvalidArgumentException(
                'Watermark must be a string'
            );
        }
    }

    /**
     * @return string
     */
    public function watermark()
    {
        return $this->watermark;
    }

    /**
     * @param float $opacity The watermark opacity value.
     * @throws InvalidArgumentException If the opacity value is not valid.
     * @return AbstractWatermarkEffect Chainable
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
     * @param string $gravity The watermark gravity value.
     * @throws InvalidArgumentException If the gravity value is not valid.
     * @return AbstractWatermarkEffect Chainable
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
     * @param integer $x The watermark x position.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return AbstractWatermarkEffect Chainable
     */
    public function setX($x)
    {
        if (!is_numeric($x)) {
            throw new InvalidArgumentException(
                'X must be a an integer'
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
     * @param integer $y The watermark y position.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return AbstractWatermarkEffect Chainable
     */
    public function setY($y)
    {
        if (!is_numeric($y)) {
            throw new InvalidArgumentException(
                'Y must be a an integer'
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
