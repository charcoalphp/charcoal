<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractSharpenEffect as AbstractSharpenEffect;

class ImagemagickSharpenEffect extends AbstractSharpenEffect
{
    /**
    * @return ImagickSharpenEffect Chainable
    */
    public function process_adaptive()
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $channel = $this->image()->convert_channel($this->channel());
        $cmd = '-channel '.$channel.' -adaptive-sharpen '.$radius.'x'.$sigma;
        $this->image()->apply_cmd($cmd);
        return $this;
    }

    /**
    * @return ImagickSharpenEffect Chainable
    */
    public function process_unsharp()
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $amount = $this->amount();
        $threshold = $this->threshold();
        $channel = $this->image()->convert_channel($this->channel());

        $cmd = '-channel '.$channel.' -unsharp '.$radius.'x'.$sigma.'+'.$amount.'+'.$threshold;
        $this->image()->apply_cmd($cmd);
        return $this;
    }

    /**
    * @return ImagickSharpenEffect Chainable
    */
    public function process_standard()
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $channel = $this->image()->convert_channel($this->channel());
        $cmd = '-channel '.$channel.' -sharpen '.$radius.'x'.$sigma;
        $this->image()->apply_cmd($cmd);
        return $this;
    }
}
