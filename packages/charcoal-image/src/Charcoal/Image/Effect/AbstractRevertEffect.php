<?php

namespace Charcoal\Image\Effect;

use InvalidArgumentException;

use Charcoal\Image\AbstractEffect;

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
     * @param string $channel The channel to revert.
     * @throws InvalidArgumentException If the channel argument is not a valid channel.
     * @return self
     */
    public function setChannel($channel)
    {
        if (!in_array($channel, $this->image()->availableChannels())) {
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
