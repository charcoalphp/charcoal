<?php

declare(strict_types=1);

namespace Charcoal\Image\Effect;

use InvalidArgumentException;
use Charcoal\Image\AbstractEffect;

/**
 * Tint (or colorize) the image with a certain color.
 */
abstract class AbstractTintEffect extends AbstractEffect
{
    private string $color = 'rgb(0,0,0)';
    /**
     * @var float $opacity;
     */
    private float $opacity = 0.5;
    private bool $midtone = true;

    /**
     * @param string $color The tint color value.
     * @throws InvalidArgumentException If the color is not a string.
     * @return AbstractTintEffect Chainable
     */
    public function setColor($color)
    {
        if (!is_string($color)) {
            throw new InvalidArgumentException(
                'Color must be a string'
            );
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
     * @param float $opacity The tint opacity value.
     * @throws InvalidArgumentException If the tint value is not numeric or lower than 0 / greater than 1.
     * @return AbstractTintEffect Chainable
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
     * @param boolean $midtone The tint midtone flag.
     * @return AbstractTintEffect Chainable
     */
    public function setMidtone($midtone)
    {
        $this->midtone = (bool) $midtone;
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
