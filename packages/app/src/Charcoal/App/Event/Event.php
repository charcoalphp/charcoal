<?php

namespace Charcoal\App\Event;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Base Event class.
 */
class Event implements StoppableEventInterface
{
    private bool $stopped = false;

    /**
     * @inheritDoc
     */
    public function isPropagationStopped(): bool
    {
        return !!$this->stopped;
    }

    /**
     * Stop the propagation of the event to further listeners.
     * The remainder of the subscribed listeners won't be dispatched
     *
     * @return void
     */
    protected function stopPropagation()
    {
        $this->stopped = true;
    }
}
