<?php

namespace Charcoal\Image;

// Intra-module (`charcoal-image`) dependencies
use \Charcoal\Image\ImageInterface;

/**
 * Image Effect Interface.
 */
interface EffectInterface
{
    /**
     * @param ImageInterface $image The parent image.
     * @return EffectInterface
     */
    public function setImage(ImageInterface $image);

    /**
     * @param array $data The effect data.
     * @return EffectInterface
     */
    public function setData(array $data);

    /**
     * @param array $data The effect data, if available.
     * @return EffectInterface
     */
    public function process(array $data = null);
}
