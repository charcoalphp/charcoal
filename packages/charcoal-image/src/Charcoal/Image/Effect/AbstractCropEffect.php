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
     * @var mixed $geometry
     */
    private $geometry;

    /**
     * @var string $gravity
     */
    private $gravity = 'center';

    /**
     * @var boolean $repage
     */
    private $repage = false;

    /**
     * @param  integer $width The crop width.
     * @throws InvalidArgumentException If the width argument is not valid.
     * @return AbstractCropEffect
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
     * @return integer
     */
    public function width()
    {
        return $this->width;
    }

    /**
     * @param  integer $height The crop height.
     * @throws InvalidArgumentException If the height argument is not valid.
     * @return AbstractCropEffect
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
     * @return integer
     */
    public function height()
    {
        return $this->height;
    }

    /**
     * The X coordinate of the cropped region's top left corner
     *
     * @param  integer $x The x-position (in pixel) of the crop.
     * @throws InvalidArgumentException If the x argument is not valid.
     * @return AbstractCropEffect
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
     * @return integer
     */
    public function x()
    {
        return $this->x;
    }

    /**
     * The Y coordinate of the cropped region's top left corner
     *
     * @param  integer $y The y-position (in pixel) of the crop.
     * @throws InvalidArgumentException If the y argumnet is not valid.
     * @return AbstractCropEffect
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
     * @return integer
     */
    public function y()
    {
        return $this->y;
    }

    /**
     * Set a complex geometry value.
     *
     * @param  mixed $geometry The image geometry.
     * @throws InvalidArgumentException If the geometry argument is not valid.
     * @return AbstractCropEffect
     */
    public function setGeometry($geometry)
    {
        if ($geometry !== null && !is_string($geometry) && !is_numeric($geometry)) {
            throw new InvalidArgumentException(
                'Geometry must be a valid crop'
            );
        }
        $this->geometry = $geometry;
        return $this;
    }

    /**
     * Retrieve the complex geometry value.
     *
     * @return mixed
     */
    public function geometry()
    {
        return $this->geometry;
    }

    /**
     * @param  string $gravity The crop gravity.
     * @throws InvalidArgumentException If the argument is not a valid gravity name.
     * @return AbstractCropEffect
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
     * @param  boolean $repage The repage image flag.
     * @return AbstractCropEffect
     */
    public function setRepage($repage)
    {
        $this->repage = !!$repage;
        return $this;
    }

    /**
     * @return boolean
     */
    public function repage()
    {
        return $this->repage;
    }

    /**
     * @param  array $data The effect data.
     * @return AbstractCropEffect
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        if ($this->geometry()) {
            $this->doCrop(0, 0, 0, 0);
            return $this;
        }

        $y = $this->y();
        $x = $this->x();
        $width  = $this->width();
        $height = $this->height();

        $this->doCrop($width, $height, $x, $y);

        return $this;
    }

    /**
     * @param  integer $width  The crop width.
     * @param  integer $height The crop height.
     * @param  integer $x      The x-position (in pixel) of the crop.
     * @param  integer $y      The y-position (in pixel) of the crop.
     * @return void
     */
    abstract protected function doCrop($width, $height, $x, $y);
}
