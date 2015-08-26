<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Rotate the image by a certain angle
*/
abstract class AbstractRotateEffect extends AbstractEffect
{
    
    /**
    * The angle of rotation, in degrees, clockwise
    * @var float $_angle
    */
    private $angle = 0;

    /**
    * The background color, for non-90-multiple rotation
    * Defaults to transparent
    * @var string $_background_color
    */
    private $background_color = 'rgb(100%, 100%, 100%, 0)';

    /**
    * @param numeric $angle
    * @throws InvalidArgumentException
    * @return AbstractRotateEffect Chainable
    */
    public function set_angle($angle)
    {
        if (!is_numeric($angle)) {
            throw new InvalidArgumentException('Angle must be a float');
        }
        $this->angle = (float)$angle;
        return $this;
    }

    /**
    * @return float
    */
    public function angle()
    {
        return $this->angle;
    }

    /**
    * @param string $color
    * @throws InvalidArgumentException
    * @return AbstractRotateEffect Chainable
    */
    public function set_background_color($color)
    {
        if (!is_string($color)) {
            throw new InvalidArgumentException('Color must be a string');
        }
        $this->background_color = $color;
        return $this;
    }

    /**
    * @return string
    */
    public function background_color()
    {
        return $this->background_color;
    }
}
