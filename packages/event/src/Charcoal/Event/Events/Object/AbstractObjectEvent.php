<?php

namespace Charcoal\Event\Events\Object;

use Charcoal\Event\Event;
use Charcoal\Model\ModelInterface;

/**
 * Base event for Object related events.
 */
abstract class AbstractObjectEvent extends Event
{
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
