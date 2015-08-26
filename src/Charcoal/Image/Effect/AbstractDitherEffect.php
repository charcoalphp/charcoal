<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* AbstractDitherEffect an image to a reduced number of colors
*/
abstract class AbstractDitherEffect extends AbstractEffect
{
    /**
    * @var integer $_colors
    */
    private $colors = 16;
    /**
    * @var string $_mode
    */
    private $mode = '';

    /**
    * @param integer $colors
    * @throws InvalidArgumentException
    * @return AbstractDitherEffect Chainable
    */
    public function set_colors($colors)
    {
        if (!is_int($colors)) {
            throw new InvalidArgumentException('Colors must be an integer');
        }
        $this->colors = $colors;
        return $this;
    }

    /**
    * @return integer
    */
    public function colors()
    {
        return $this->colors;
    }

    /**
    * @param string $mode
    * @throws InvalidArgumentException
    * @return AbstractDitherEffect Chainable
    */
    public function set_mode($mode)
    {
        $allowed_modes = [
            '', // Quantize
            'threshold',
            'checks',
            'o2x2',
            'o3x3',
            'o4x4',
            'o8x8',
            'h4x4a',
            'h6x6a',
            'h8x8a',
            'h4x4o',
            'h6x6o',
            'h8x8o',
            'h16x16o',
            'c5x5b',
            'c5x5w',
            'c6x6b',
            'c6x6w',
            'c7x7b',
            'c7x7w'
        ];
        if (!in_array($mode, $allowed_modes)) {
            throw new InvalidArgumentException('Invalid dither mode');
        }
        $this->mode = $mode;
        return $this;
    }

    /**
    * @return string
    */
    public function mode()
    {
        return $this->mode;
    }
}
