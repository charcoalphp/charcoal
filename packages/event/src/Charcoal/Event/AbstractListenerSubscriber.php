<?php

namespace Charcoal\Event;

use Charcoal\Factory\FactoryInterface;
use League\Event\ListenerRegistry;
use League\Event\ListenerSubscriber;
use Pimple\Container;

/**
 * Base class for listener Subscriber
 *
 * Subscribers are classes that allows registering multiple event listeners in grouped and organized manner.
 */
abstract class AbstractListenerSubscriber implements ListenerSubscriber
{
    protected FactoryInterface $listenerFactory;

    /**
     * @param Container $container
     * @return void
     */
    public function setDependencies(Container $container)
    {
        $this->listenerFactory = $container['event/listener/factory'];
    }

    /**
     * @param $listener
     * @return EventListenerInterface
     */
    protected function createListener($listener): EventListenerInterface
    {
        return $this->listenerFactory->create($listener);
    }

    abstract public function subscribeListeners(ListenerRegistry $acceptor): void;
}
