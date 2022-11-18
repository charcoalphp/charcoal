<?php

namespace Charcoal\Model\Events;

use Charcoal\Event\Event;
use Charcoal\Event\InterruptableEventInterface;
use Charcoal\Event\InterruptableEventTrait;
use Charcoal\Model\ModelInterface;

/**
 * Base event for Object related events.
 */
abstract class AbstractModelEvent extends Event implements InterruptableEventInterface
{
    use InterruptableEventTrait;

    private ModelInterface $object;

    /**
     * @param ModelInterface $object A charcoal object .
     */
    public function __construct(ModelInterface $object)
    {
        $this->object = $object;
    }

    /**
     * @return ModelInterface
     */
    public function getObject(): ModelInterface
    {
        return $this->object;
    }
}
