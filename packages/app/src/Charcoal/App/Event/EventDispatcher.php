<?php

namespace Charcoal\App\Event;

use League\Event\EventDispatcher as LeagueEventDispatcher;
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
            $this->logger->notice('Event dispatched : [' . get_class($event) . ']');
        }

        return parent::dispatch($event);
    }
}
