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
    * @var string $_color
    */
    private $_color = 'rgb(0,0,0)';
    /**
    * @var float $_opacity;
    */
    private $_opacity = 0.5;
    /**
    * @var boolean $_midtone
    */
    private $_midtone = true;

    /**
    * @param array $data
    * @return AbstractTintEffect Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['color']) && $data['color'] !== null) {
            $this->set_color($data['color']);
        }
        if (isset($data['opacity']) && $data['opacity'] !== null) {
            $this->set_opacity($data['opacity']);
        }
        if (isset($data['midtone']) && $data['midtone'] !== null) {
            $this->set_midtone($data['midtone']);
        }
        return $this;
    }

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
        $this->_color = $color;
        return $this;
    }

    /**
    * @return string
    */
    public function color()
    {
        return $this->_color;
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
    * @param boolean $midtone
    * @throws InvalidArgumentException
    * @return AbstractTintEffect Chainable
    */
    public function set_midtone($midtone)
    {
        if (!is_bool($midtone)) {
            throw new InvalidArgumentException('Midtone must be a boolean');
        }
        $this->_midtone = $midtone;
        return $this;
    }

    /**
    * @return boolean
    */
    public function midtone()
    {
        return $this->_midtone;
    }

}
