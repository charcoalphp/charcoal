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
    * @param int $width
    * @throws InvalidArgumentException
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
    * @param integer $height
    * @throws InvalidArgumentException
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
    * @param integer $x
    * @throws InvalidArgumentException
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
    * @param integer $y
    * @throws InvalidArgumentException
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
    public function x()
    {
        return $this->x;
    }

    /**
    * @param array $data
    * @throws Exception
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

        $this->doCrop($x, $y, $width, $height);

        return $this;
    }

    /**
    * @param integer $width
    * @param integer $height
    * @param boolean $best_fit
    * @return void
    */
    abstract protected function doCrop($x, $y, $width, $height);
}
