<?php

namespace Charcoal\Event;

/**
 * Trait: StoppableEventTrait
 * @package Charcoal\Event
 */
trait StoppableEventTrait
{
    private bool $propagationStopped = false;

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the Dispatcher to determine if the
     * previous listener halted propagation.
     *
     * @return bool
     *   True if the Event is complete and no further listeners should be called.
     *   False to continue calling listeners.
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stop the propagation of the event to further listeners.
     * The remainder of the subscribed listeners won't be dispatched
     *
     * @return void
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }
}
