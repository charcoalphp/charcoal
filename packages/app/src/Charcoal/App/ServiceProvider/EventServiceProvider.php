<?php

namespace Charcoal\App\ServiceProvider;

use Charcoal\App\Event\EventDispatcher;
use Charcoal\App\Event\EventListenerInterface;
use Charcoal\Factory\FactoryInterface;
use Charcoal\Factory\GenericFactory;
use League\Event\ListenerSubscriber;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Event Service Provider. Configures and provides a PDO service to a container.
 */
class EventServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container A service container.
     * @return void
     */
    public function register(Container $container)
    {
        /**
         * @param Container $container Pimple DI container.
         * @return EventDispatcherInterface
         */
        $container['event/dispatcher'] = function (Container $container): EventDispatcherInterface {
            $dispatcher = new EventDispatcher();
            $dispatcher->setLogger($container['logger']);

            $this->registerEventListeners($dispatcher, $container);
            $this->registerListenerSubscribers($dispatcher, $container);

            return $dispatcher;
        };

        /**
         * @param Container $container
         * @return array
         */
        $container['event/listeners'] = function (Container $container): array {
            return ($container['config']->get('events.listeners') ?? []);
        };

        /**
         * Subscribers are classes that implements `\League\Event\ListenerSubscriber`
         * It allows to subscribe many grouped listeners at once.
         *
         * @param Container $container
         * @return array
         */
        $container['event/subscribers'] = function (Container $container): array {
            return ($container['config']->get('events.subscribers') ?? []);
        };

        /**
         * @param Container $container The Pimple DI container.
         * @return FactoryInterface
         */
        $container['event/listener/factory'] = function (Container $container) {
            return new GenericFactory([
                'base_class'       => EventListenerInterface::class,
                'resolver_options' => [
                    'suffix' => 'Listener'
                ],
                'callback'         => function ($listener) use ($container) {
                    $listener->setDependencies($container);
                }
            ]);
        };

        /**
         * @param Container $container The Pimple DI container.
         * @return FactoryInterface
         */
        $container['event/listener-subscriber/factory'] = function (Container $container) {
            return new GenericFactory([
                'base_class'       => ListenerSubscriber::class,
                'resolver_options' => [
                    'suffix' => 'Subscriber'
                ],
                'callback'         => function ($subscriber) use ($container) {
                    if (is_callable([$subscriber, 'setDependencies'])) {
                        $subscriber->setDependencies($container);
                    }
                }
            ]);
        };
    }

    /**
     * @param EventDispatcherInterface $dispatcher Psr-14 Event Dispatcher Interface
     * @param Container                $container  Pimple DI container
     * @return void
     */
    private function registerEventListeners(EventDispatcherInterface $dispatcher, Container $container)
    {
        /**
         * @var array<string, array<class-string<EventListenerInterface, mixed>> $container['event/listeners']
         */
        foreach ($container['event/listeners'] as $event => $listeners) {
            if (!is_iterable($listeners)) {
                throw new \InvalidArgumentException();
            }

            foreach ($listeners as $listener => $options) {
                if (!is_string($listener)) {
                    throw new \InvalidArgumentException();
                }

                $listener = $container['event/listener/factory']->create($listener);

                $priority = ($options['priority'] ?? 0);
                $once     = ($options['once'] ?? false);

                if ($once) {
                    $dispatcher->subscribeOnceTo($event, $listener, $priority);
                    continue;
                }

                $dispatcher->subscribeTo($event, $listener, $priority);
            }
        }
    }

    /**
     * @param EventDispatcherInterface $dispatcher Psr-14 Event Dispatcher Interface
     * @param Container                $container  Pimple DI container
     * @return void
     */
    private function registerListenerSubscribers(EventDispatcherInterface $dispatcher, Container $container)
    {
        foreach ($container['event/subscribers'] as $subscriber) {
            if (!is_string($subscriber) || !class_exists($subscriber)) {
                throw new \InvalidArgumentException();
            }

            $subscriber = $container['event/listener-subscriber/factory']->create($subscriber);

            $dispatcher->subscribeListenersFrom($subscriber);
        }
    }
}
