<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractSharpenEffect;

/**
 * Sharpen Effect for the Imagemagick driver.
 */
class ImagemagickSharpenEffect extends AbstractSharpenEffect
{
    public function processAdaptive(): static
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel ' . $channel . ' -adaptive-sharpen ' . $radius . 'x' . $sigma;
        $this->image()->applyCmd($cmd);
        return $this;
    }

    public function processUnsharp(): static
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $amount = $this->amount();
        $threshold = $this->threshold();
        $channel = $this->image()->convertChannel($this->channel());

        $cmd = '-channel ' . $channel . ' -unsharp ' . $radius . 'x' . $sigma . '+' . $amount . '+' . $threshold;
        $this->image()->applyCmd($cmd);
        return $this;
    }

    public function processStandard(): static
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel ' . $channel . ' -sharpen ' . $radius . 'x' . $sigma;
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
