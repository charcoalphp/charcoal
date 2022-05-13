<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
 * Rotate the image by a certain angle
 */
abstract class AbstractRotateEffect extends AbstractEffect
{

    /**
     * The angle of rotation, in degrees, clockwise
     * @var float $Angle
     */
    private $angle = 0;

    /**
     * The background color, for non-90-multiple rotation
     * Defaults to transparent
     * @var string $BackgroundColor
     */
    private $backgroundColor = 'rgb(100%, 100%, 100%, 0)';

    /**
     * @param float $angle The rotation angle.
     * @throws InvalidArgumentException If the angle argument is not numeric.
     * @return AbstractRotateEffect Chainable
     */
    public function setAngle($angle)
    {
        if (!is_numeric($angle)) {
            throw new InvalidArgumentException(
                'Angle must be a float'
            );
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
     * @param string $color The background color, for non-90 rotations.
     * @throws InvalidArgumentException If the color argument is not a string.
     * @return AbstractRotateEffect Chainable
     */
    public function setBackgroundColor($color)
    {
        if (!is_string($color)) {
            throw new InvalidArgumentException(
                'Color must be a string'
            );
        }
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * @return string
     */
    public function backgroundColor()
    {
        return $this->backgroundColor;
    }
}
