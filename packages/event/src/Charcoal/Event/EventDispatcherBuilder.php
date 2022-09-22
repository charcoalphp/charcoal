<?php

namespace Charcoal\Event;

use InvalidArgumentException;
use League\Event\ListenerSubscriber;
use Pimple\Container;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Builder
 *
 * Helps in the process of building an Event Dispatcher.
 */
class EventDispatcherBuilder
{
    /**
     * A Pimple dependency-injection container to fulfill the required services.
     * @var Container $container
     */
    protected Container $container;

    /**
     * @param Container        $container The DI container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $listeners
     * @param array $subscribers
     * @return EventDispatcher
     */
    public function build(array $listeners = [], array $subscribers = []): EventDispatcher
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->setLogger($this->container['logger']);

        $this->registerEventListeners($dispatcher, $listeners);
        $this->registerListenerSubscribers($dispatcher, $subscribers);

        return $dispatcher;
    }

    /**
     * @param EventDispatcherInterface $dispatcher       Psr-14 Event Dispatcher Interface
     * @param array<string, array>     $listenersByEvent Array of EventListenerInterface attached to event.
     * @return void
     */
    private function registerEventListeners(EventDispatcherInterface $dispatcher, array $listenersByEvent)
    {
        foreach ($listenersByEvent as $event => $listeners) {
            if (!is_iterable($listeners)) {
                throw new InvalidArgumentException(sprintf(
                    'Expected iterable map of event listeners for [%s]',
                    $event
                ));
            }

            foreach ($listeners as $listener => $options) {
                if (!is_string($listener)) {
                    throw new InvalidArgumentException(sprintf(
                        'Expected event listener class string as map key for [%s]',
                        $event
                    ));
                }

                $listener = $this->container['event/listener/factory']->create($listener);

                $priority = ($options['priority'] ?? 0);
                $once     = ($options['once'] ?? false);

                if ($once) {
                    $dispatcher->subscribeOnceTo($event, $listener, $priority);
                } else {
                    $dispatcher->subscribeTo($event, $listener, $priority);
                }
            }
        }
    }

    /**
     * @param EventDispatcherInterface                $dispatcher  Psr-14 Event Dispatcher Interface
     * @param array<class-string<ListenerSubscriber>> $subscribers Pimple DI container
     * @return void
     */
    private function registerListenerSubscribers(EventDispatcherInterface $dispatcher, array $subscribers)
    {
        foreach ($subscribers as $subscriber) {
            if (!is_string($subscriber) || !class_exists($subscriber)) {
                throw new InvalidArgumentException(sprintf(
                    'Expected event subscriber as class string, received %s',
                    (is_string($subscriber) ? $subscriber : gettype($subscriber))
                ));
            }

            $subscriber = $this->container['event/listener-subscriber/factory']->create($subscriber);

            $dispatcher->subscribeListenersFrom($subscriber);
        }
    }
}
