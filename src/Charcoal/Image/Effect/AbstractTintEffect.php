<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Tint (or colorize) the image with a certain color.
*/
abstract class AbstractTintEffect extends AbstractEffect
{
    /**
    * @var string $color
    */
    private $color = 'rgb(0,0,0)';
    /**
    * @var float $opacity;
    */
    private $opacity = 0.5;
    /**
    * @var boolean $midtone
    */
    private $midtone = true;

    /**
    * @param string $color
    * @throws InvalidArgumentException
    * @return AbstractTintEffect Chainable
    */
    public function set_color($color)
    {
        if (!is_string($color)) {
            throw new InvalidArgumentException('Color must be a string');
        }
        $this->color = $color;
        return $this;
    }

    /**
    * @return string
    */
    public function color()
    {
        return $this->color;
    }

    /**
    * @param float $opacity
    * @throws InvalidArgumentException
    * @return AbstractTintEffect Chainable
    */
    public function set_opacity($opacity)
    {
        if (!is_numeric($opacity) || ($opacity < 0) || ( $opacity > 1)) {
            throw new InvalidArgumentException('Opacity must be a float between 0.0 and 1.0');
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
    * @param boolean $midtone
    * @throws InvalidArgumentException
    * @return AbstractTintEffect Chainable
    */
    public function set_midtone($midtone)
    {
        if (!is_bool($midtone)) {
            throw new InvalidArgumentException('Midtone must be a boolean');
        }
        $this->midtone = $midtone;
        return $this;
    }

    /**
    * @return boolean
    */
    public function midtone()
    {
        return $this->midtone;
    }
}
