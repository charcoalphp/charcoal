<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractSharpenEffect;

/**
 * Sharpen Effect for the Imagemagick driver.
 */
class ImagemagickSharpenEffect extends AbstractSharpenEffect
{
    /**
     * @return self
     */
    public function processAdaptive()
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel '.$channel.' -adaptive-sharpen '.$radius.'x'.$sigma;
        $this->image()->applyCmd($cmd);
        return $this;
    }

    /**
     * @return self
     */
    public function processUnsharp()
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $amount = $this->amount();
        $threshold = $this->threshold();
        $channel = $this->image()->convertChannel($this->channel());

        $cmd = '-channel '.$channel.' -unsharp '.$radius.'x'.$sigma.'+'.$amount.'+'.$threshold;
        $this->image()->applyCmd($cmd);
        return $this;
    }

    /**
     * @return self
     */
    public function processStandard()
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel '.$channel.' -sharpen '.$radius.'x'.$sigma;
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
