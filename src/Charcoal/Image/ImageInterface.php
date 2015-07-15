<?php

namespace Charcoal\Image;

interface ImageInterface
{
    /**
    * @param array $data
    * @return ImageInterface Chainable
    */
    public function set_data(array $data);

    /**
    * @param string $source
    * @throws InvalidArgumentException
    * @return ImageInterface Chainable
    */
    public function set_source($source);

    /**
    * @return string
    */
    public function source();

    /**
    * @param string $target
    * @throws InvalidArgumentException
    * @return ImageInterface Chainable
    */
    public function set_target($target);

    /**
    * @return string
    */
    public function target();
    
    /**
    * @param array $effects
    * @return ImageInterface Chainable
    */
    public function set_effects(array $effects);

    /**
    * @return array The array of `EffectInterface` effects
    */
    public function effects();

    /**
    * @param array|EffectInterface $effect
    * @throws InvalidArgumentException
    * @return ImageInterface Chainable
    */
    public function add_effect($effect);

    /**
    * @param array $effects
    * @return ImageInterface Chainable
    */
    public function process(array $effects = null);

    /**
    * @param array|EffectInterface $effect
    * @return ImageInterface Chainable
    */
    public function process_effect($effect);

    /**
    * Create a blank canvas of a given size, with a given background color.
    *
    * @param integer $width  Image size, in pixels
    * @param integer $height Image height, in pixels
    * @param string  $color  Default to transparent.
    * @throws InvalidArgumentException
    * @return ImageInterface Chainable
    */
    public function create($width, $height, $color = 'rgb(100%, 100%, 100%, 0)');

    /**
    * Open an image file
    *
    * @param string $source The source path / filename
    * @throws InvalidArgumentException
    * @return ImageInterface Chainable
    */
    public function open($source = null);

    /**
    * Save an image to a target.
    * If no target is set, the original source will be owerwritten
    *
    * @param string $target The target path / filename
    * @throws InvalidArgumentException
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
    * @throws Excception
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
    public function is_horizontal();

    /**
    * @return boolean
    */
    public function is_vertical();

    /**
    * @return boolean
    */
    public function is_square();
}
