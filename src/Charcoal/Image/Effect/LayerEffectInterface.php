<?php

namespace Charcoal\Image\Effect;

/**
 * Layer effect defines image effects that stack an image on top of another.
 *
 * The "layer" can therefore have the following properties:
 * - opacity
 * - gravity
 * - x
 * - y
 */
interface LayerEffectInterface
{
    /**
     * @param float $opacity The mask opacity.
     * @throws InvalidArgumentException If the mask opacity is not a numeric value or not between 0.0 and 1.0.
     * @return AbstractMaskEffect Chainable
     */
    public function setOpacity($opacity);

    /**
     * @return float
     */
    public function opacity();

    /**
     * @param string $gravity The mask gravity.
     * @throws InvalidArgumentException If the argument is not a valid gravity name.
     * @return AbstractMaskEffect Chainable
     */
    public function setGravity($gravity);

    /**
     * @return string
     */
    public function gravity();

    /**
     * @param integer $x The mask X position.
     * @throws InvalidArgumentException If the position is not a numeric value.
     * @return AbstractMaskEffect Chainable
     */
    public function setX($x);

    /**
     * @return float
     */
    public function x();

    /**
     * @param integer $y The Y position.
     * @return AbstractMaskEffect Chainable
     */
    public function setY($y);

    /**
     * @return float
     */
    public function y();
}
