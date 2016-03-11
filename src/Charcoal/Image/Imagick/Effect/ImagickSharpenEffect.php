<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractSharpenEffect;

/**
 * Sharpen Effect for Imagick driver.
 */
class ImagickSharpenEffect extends AbstractSharpenEffect
{
    /**
     * @return ImagickSharpenEffect Chainable
     */
    public function processAdaptive()
    {
        $channel = $this->image()->imagickChannel($this->channel());
        $this->image()->imagick()->adaptiveAbstractSharpenEffectImage($this->radius(), $this->sigma(), $channel);
        return $this;
    }

    /**
     * @return ImagickSharpenEffect Chainable
     */
    public function processUnsharp()
    {
        $radius = $this->radius();
        $sigma = $this->sigma();
        $amount = $this->amount();
        $threshold = $this->threshold();
        $channel = $this->image()->imagickChannel($this->channel());

        $this->image()->imagick()->unsharpMaskImage($radius, $sigma, $amount, $threshold, $channel);

        return $this;
    }

    /**
     * @return ImagickSharpenEffect Chainable
     */
    public function processStandard()
    {
        $channel = $this->image()->imagickChannel($this->channel());
        $this->image()->imagick()->sharpenImage($this->radius(), $this->sigma(), $channel);
        return $this;
    }
}
