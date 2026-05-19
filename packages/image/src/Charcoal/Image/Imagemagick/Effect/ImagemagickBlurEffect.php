<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractBlurEffect;

/**
 * Blur Effect for the Imagemagick driver.
 */
class ImagemagickBlurEffect extends AbstractBlurEffect
{
    public function processAdaptive(): static
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel ' . $channel . ' -adaptive-blur ' . $this->radius() . 'x' . $this->sigma();
        $this->image()->applyCmd($cmd);
        return $this;
    }

    public function processGaussian(): static
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel ' . $channel . ' -gaussian-blur ' . $this->radius() . 'x' . $this->sigma();
        $this->image()->applyCmd($cmd);
        return $this;
    }

    public function processMotion(): static
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel ' . $channel . ' -motion-blur ' . $this->radius() . 'x' . $this->sigma() . '+' . $this->angle();
        $this->image()->applyCmd($cmd);
        return $this;
    }

    public function processRadial(): static
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel ' . $channel . ' -rotational-blur ' . $this->angle();
        $this->image()->applyCmd($cmd);
        return $this;
    }

    public function processSoft(): static
    {
        $cmd = '-define convolve:scale=60,40% -morphology Convolve \'Gaussian:' . $this->radius() . 'x' . $this->sigma() . '\'';
        $this->image()->applyCmd($cmd);
        return $this;
    }

    public function processStandard(): static
    {
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel ' . $channel . ' -blur ' . $this->radius() . 'x' . $this->sigma();
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
