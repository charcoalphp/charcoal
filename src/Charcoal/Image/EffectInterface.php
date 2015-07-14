<?php

namespace Charcoal\Image;

interface EffectInterface
{

    /**
    * @param ImageInterface $image
    * @return EffectInterface
    */
    public function set_image(ImageInterface $image);

    /**
    * @param array $data
    * @return EffectInterface
    */
    public function set_data(array $data);

    /**
    * @param array $data
    * @return EffectInterface
    */
    public function process(array $data = null);
}
