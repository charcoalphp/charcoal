<?php

namespace Charcoal\App;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Trait: EventDispatcherTrait
 * @package Charcoal\App
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
}
