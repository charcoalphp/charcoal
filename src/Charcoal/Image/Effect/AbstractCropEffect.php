<?php

namespace Charcoal\Image\Effect;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
 * Resize an image to given dimensions
 */
abstract class AbstractCropEffect extends AbstractEffect
{
    /**
     * @var integer $x
     */
    private $x = 0;
    /**
     * @var integer $y
     */
    private $y = 0;
    /**
     * @var integer $width
     */
    private $width = 0;
    /**
     * @var integer $height
     */
    private $height = 0;

    /**
     * @param integer $width The crop width.
     * @throws InvalidArgumentException If the width argument is not valid.
     * @return Rotate Chainable
     */
    public function setWidth($width)
    {
        if (!is_int($width) || ($width < 0)) {
            throw new InvalidArgumentException(
                'Width must be a a positive integer'
            );
        }
        $this->width = $width;
        return $this;
    }

    /**
     * @return float
     */
    public function width()
    {
        return $this->width;
    }

    /**
     * @param integer $height The crop height.
     * @throws InvalidArgumentException If the height argument is not valid.
     * @return $this Chainable
     */
    public function setHeight($height)
    {
        if (!is_int($height) || ($height < 0)) {
            throw new InvalidArgumentException(
                'Height must be a positive integer'
            );
        }
        $this->height = $height;
        return $this;
    }

    /**
     * @return float
     */
    public function height()
    {
        return $this->height;
    }

    /**
     * The X coordinate of the cropped region's top left corner
     *
     * @param integer $x The x-position (in pixel) of the crop.
     * @throws InvalidArgumentException If the x argument is not valid.
     * @return $this Chainable
     */
    public function setX($x)
    {
        if (!is_int($x) || ($x < 0)) {
            throw new InvalidArgumentException(
                'Height must be a positive integer'
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
     * The Y coordinate of the cropped region's top left corner
     *
     * @param integer $y The y-position (in pixel) of the crop.
     * @throws InvalidArgumentException If the y argumnet is not valid.
     * @return $this Chainable
     */
    public function setY($y)
    {
        if (!is_int($y) || ($y < 0)) {
            throw new InvalidArgumentException(
                'Height must be a positive integer'
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

    /**
     * @param array $data The effect data.
     * @return AbstractResizeEffect Chainable
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $y = $this->y();
        $x = $this->x();
        $width = $this->width();
        $height = $this->height();

        $this->doCrop($width, $height, $x, $y);

        return $this;
    }

    /**
     * @param integer $width  The crop width.
     * @param integer $height The crop height.
     * @param integer $x      The x-position (in pixel) of the crop.
     * @param integer $y      The y-position (in pixel) of the crop.
     * @return void
     */
    abstract protected function doCrop($width, $height, $x, $y);
}
