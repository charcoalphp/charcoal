<?php

namespace Charcoal\Event;

use League\Event\EventDispatcher as LeagueEventDispatcher;
use League\Event\HasEventName;
use Psr\Log\LoggerAwareTrait;

/**
 * Event Dispatcher
 *
 * Extension of \League\Event\EventDispatcher to add extra features.
 */
class EventDispatcher extends LeagueEventDispatcher
{
    use LoggerAwareTrait;

    public function dispatch(object $event): object
    {
        if ($this->logger) {
            if ($event instanceof HasEventName) {
                $this->logger->notice('Event [' . $event->eventName() . '] dispatched', [
                    'event' => get_class($event),
                ]);
            } else {
                $this->logger->notice('Event dispatched', [
                    'event' => get_class($event),
                ]);
            }
        }

        return parent::dispatch($event);
    }
}
