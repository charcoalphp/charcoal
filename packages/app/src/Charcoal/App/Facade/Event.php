<?php

namespace Charcoal\App\Facade;

use League\Event\EventGenerator;
use League\Event\ListenerPriority;
use League\Event\ListenerSubscriber;

/**
 * Facade: Event Dispatcher
 *
 * Alias for the 'admin/event/dispatcher' container service.
 * Provides access to the admin event dispatcher.
 *
 * @method static object dispatch(object $event)
 * @method static void dispatchGeneratedEvents(EventGenerator $generator)
 * @method static void subscribeTo(string $event, callable $listener, int $priority = ListenerPriority::NORMAL)
 * @method static void subscribeOnceTo(string $event, callable $listener, int $priority = ListenerPriority::NORMAL)
 * @method static void subscribeListenersFrom(ListenerSubscriber $subscriber)
 *
 * @see \Charcoal\Event\EventDispatcher
 * @see \League\Event\EventDispatcher
 */
class Event extends Facade
{
    protected static function getFacadeName(): string
    {
        return 'admin/event/dispatcher';
    }
}
