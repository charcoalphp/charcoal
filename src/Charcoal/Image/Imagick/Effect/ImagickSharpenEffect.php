<?php

namespace Charcoal\Image\Imagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractSharpenEffect as AbstractSharpenEffect;

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
        $channel = $this->image()->imagick_channel($this->channel());
        $this->image()->unsharpMaskImage($this->radius(), $this->sigma(), $this->amount(), $this->threshold(), $channel);
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
