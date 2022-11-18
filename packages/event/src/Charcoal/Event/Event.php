<?php

namespace Charcoal\Event;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Base Event class.
 */
class Event implements StoppableEventInterface
{
    use StoppableEventTrait;
}
