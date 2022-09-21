<?php

namespace Charcoal\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Trait: EventDispatcherTrait
 * @package Charcoal\App\Event
 */
trait EventDispatcherTrait
{
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher EventDispatcher for EventDispatcherTrait.
     * @return self
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param object $event
     *   The object to process.
     *
     * @return object
     *   The Event that was passed, now modified by listeners.
     */
    protected function dispatchEvent(object $event): object
    {
        return $this->getEventDispatcher()->dispatch($event);
    }

    /**
     * @param array $events
     * @return void
     */
    protected function dispatchEvents(array $events)
    {
        array_map([$this, 'dispatchEvent'], $events);
    }
}
