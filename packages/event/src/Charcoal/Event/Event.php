<?php

namespace Charcoal\Event;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Base Event class.
 */
class Event implements StoppableEventInterface
{
    private bool $propagationStopped = false;

    /**
     * @inheritDoc
     */
    public function isPropagationStopped(): bool
    {
        return !!$this->propagationStopped;
    }

    /**
     * Stop the propagation of the event to further listeners.
     * The remainder of the subscribed listeners won't be dispatched
     *
     * @return void
     */
    protected function stopPropagation()
    {
        $this->propagationStopped = true;
    }
}
