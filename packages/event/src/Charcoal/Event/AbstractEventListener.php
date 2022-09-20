<?php

namespace Charcoal\Event;

use Pimple\Container;
use Psr\Log\LoggerAwareTrait;

/**
 * Abstract Event Listener
 *
 * Starting point to create an eventListener.
 */
abstract class AbstractEventListener implements EventListenerInterface
{
    use LoggerAwareTrait;

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        $this->setLogger($container['logger']);
    }
}
