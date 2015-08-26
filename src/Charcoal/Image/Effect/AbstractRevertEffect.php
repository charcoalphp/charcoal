<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
* Revert (negate) the image's colors.
*/
abstract class AbstractRevertEffect extends AbstractEffect
{
    /**
    * @var string $channel
    */
    private $channel = 'all';

    /**
    * @param string $channel
    * @throws InvalidArgumentException
    * @return AbstractBlurEffect Chainable
    */
    public function set_channel($channel)
    {
        if (!in_array($channel, $this->image()->available_channels())) {
            throw new InvalidArgumentException(
                'Channel is not valid'
            );
        }
        $this->channel = $channel;
        return $this;
    }
    
    /**
    * @return string
    */
    public function channel()
    {
        return $this->channel;
    }
}
