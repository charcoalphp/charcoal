<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
 * AbstractDitherEffect an image to a reduced number of colors
 */
abstract class AbstractDitherEffect extends AbstractEffect
{
    /**
     * @var integer $colors
     */
    private $colors = 16;
    /**
     * @var string $mode
     */
    private $mode = '';

    /**
     * @param integer $colors The numver of dither colors.
     * @throws InvalidArgumentException If the argument is not numeric.
     * @return AbstractDitherEffect Chainable
     */
    public function setColors($colors)
    {
        if (!is_numeric($colors)) {
            throw new InvalidArgumentException(
                'Colors must be an integer'
            );
        }
        $this->colors = (int)$colors;
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
     * @param string $mode The dither mode.
     * @throws InvalidArgumentException If the argument is not a valid dither mode.
     * @return AbstractDitherEffect Chainable
     */
    public function setMode($mode)
    {
        $allowedModes = [
            '',
// Quantize
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
        if (!in_array($mode, $allowedModes)) {
            throw new InvalidArgumentException(
                'Invalid dither mode'
            );
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
