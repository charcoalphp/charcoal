<?php

namespace Charcoal\App\Event;

/**
 * Interface: EventListenerInterface
 *
 * @package Charcoal\App\Event
 */
interface EventListenerInterface
{
    /**
     * @param object $event The event object being fired.
     * @return void
     */
    public function __invoke(object $event);
}
