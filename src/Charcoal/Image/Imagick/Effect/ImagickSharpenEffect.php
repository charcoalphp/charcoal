<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractSharpenEffect;

class ImagickSharpenEffect extends AbstractSharpenEffect
{
    /**
    * @return ImagickSharpenEffect Chainable
    */
    public function process_adaptive()
    {
        $channel = $this->image()->imagick_channel($this->channel());
        $this->image()->imagick()->adaptiveAbstractSharpenEffectImage($this->radius(), $this->sigma(), $channel);
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
        $channel = $this->image()->imagick_channel($this->channel());

        $this->image()->unsharpMaskImage($radius, $sigma, $amount, $threshold, $channel);

        return $this;
    }

    /**
    * @return ImagickSharpenEffect Chainable
    */
    public function process_standard()
    {
        $channel = $this->image()->imagick_channel($this->channel());
        $this->image()->imagick()->sharpenImage($this->radius(), $this->sigma(), $channel);
        return $this;
    }
}
