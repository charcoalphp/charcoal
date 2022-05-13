<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractBlurEffect;

/**
 * Blur Effect for the Imagemagick driver.
 */
class ImagemagickBlurEffect extends AbstractBlurEffect
{
    /**
     * @return self
     */
    public function processAdaptive()
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel '.$channel.' -adaptive-blur '.$this->radius().'x'.$this->sigma();
        $this->image()->applyCmd($cmd);
        return $this;
    }

    /**
     * @return self
     */
    public function processGaussian()
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel '.$channel.' -gaussian-blur '.$this->radius().'x'.$this->sigma();
        $this->image()->applyCmd($cmd);
        return $this;
    }

    /**
     * @return self
     */
    public function processMotion()
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel '.$channel.' -motion-blur '.$this->radius().'x'.$this->sigma().'+'.$this->angle();
        $this->image()->applyCmd($cmd);
        return $this;
    }

    /**
     * @return self
     */
    public function processRadial()
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel '.$channel.' -radial-blur '.$this->angle();
        $this->image()->applyCmd($cmd);
        return $this;
    }

    /**
     * @return self
     */
    public function processSoft()
    {
        $cmd = '-define convolve:scale=60,40% -morphology Convolve \'Gaussian:'.$this->radius().'x'.$this->sigma().'\'';
        $this->image()->applyCmd($cmd);
        return $this;
    }

    /**
     * @return self
     */
    public function processStandard()
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel '.$channel.' -blur '.$this->radius().'x'.$this->sigma();
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
