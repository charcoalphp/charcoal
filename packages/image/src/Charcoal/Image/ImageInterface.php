<?php

namespace Charcoal\Image;

interface ImageInterface
{
    /**
     * @param array $data The image data.
     * @return ImageInterface Chainable
     */
    public function setData(array $data);

    /**
     * @param string $source The source.
     * @return ImageInterface Chainable
     */
    public function setSource($source);

    /**
     * @return string
     */
    public function source();

    /**
     * @param string $target The target.
     * @return ImageInterface Chainable
     */
    public function setTarget($target);

    /**
     * @return string
     */
    public function target();

    /**
     * @param array $effects The list of effects.
     * @return ImageInterface Chainable
     */
    public function setEffects(array $effects);

    /**
     * @return EffectInterface[] The array of `EffectInterface` effects
     */
    public function effects();

    /**
     * @param array|EffectInterface $effect The effect, or effect options, to add.
     * @return ImageInterface Chainable
     */
    public function addEffect($effect);

    /**
     * @param array $effects Extra effects to process.
     * @return ImageInterface Chainable
     */
    public function process(array $effects = null);

    /**
     * @param array|EffectInterface $effect An effect to process.
     * @return ImageInterface Chainable
     */
    public function processEffect($effect);

    /**
     * Create a blank canvas of a given size, with a given background color.
     *
     * @param integer $width  Image size, in pixels.
     * @param integer $height Image height, in pixels.
     * @param string  $color  Default to transparent.
     * @return ImageInterface Chainable
     */
    public function create($width, $height, $color = 'rgb(100%, 100%, 100%, 0)');

    /**
     * Open an image file
     *
     * @param string $source The source path / filename.
     * @return ImageInterface Chainable
     */
    public function open($source = null);

    /**
     * Save an image to a target.
     * If no target is set, the original source will be owerwritten
     *
     * @param string $target The target path / filename.
     * @return ImageInterface Chainable
     */
    public function save($target = null);

    /**
     * Get the image's width, in pixels
     *
     * @return integer
     */
    public function width();

    /**
     * Get the image's height, in pixels
     *
     * @return integer
     */
    public function height();

    /**
     * Get the image's ratio (width / height)
     *
     * @return float
     */
    public function ratio();

    /**
     * Orientation can be "horizontal", "vertical" or "square"
     *
     * @return string
     */
    public function orientation();

    /**
     * @return boolean
     */
    public function isHorizontal();

    /**
     * @return boolean
     */
    public function isVertical();

    /**
     * @return boolean
     */
    public function isSquare();
}
