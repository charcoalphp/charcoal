<?php

namespace Charcoal\Event\ServiceProvider;

use Charcoal\Event\EventDispatcherBuilder;
use Charcoal\Event\EventListenerInterface;
use Charcoal\Factory\FactoryInterface;
use Charcoal\Factory\GenericFactory;
use League\Event\ListenerSubscriber;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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
         * @param  Container $container A service container.
         * @return EventDispatcherBuilder
         */
        $container['event/dispatcher/builder'] = function (Container $container) {
            return new EventDispatcherBuilder($container);
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
                    if (is_callable([$listener, 'setDependencies'])) {
                        $listener->setDependencies($container);
                    }
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
}
