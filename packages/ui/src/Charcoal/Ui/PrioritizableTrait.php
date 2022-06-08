<?php

namespace Charcoal\Ui;

use InvalidArgumentException;

/**
 * Provides an entity with a priority level or sorting index.
 *
 * Implementation of {@see \Charcoal\Ui\PrioritizableInterface}.
 */
trait PrioritizableTrait
{
    /**
     * Priority level of the entity.
     *
     * @var integer
     */
    private $priority = 0;

    /**
     * Set the entity's priority index.
     *
     * @param  integer $priority An index, for sorting.
     * @throws InvalidArgumentException If the priority is not an integer.
     * @return self
     */
    public function setPriority($priority)
    {
        if (!is_numeric($priority)) {
            throw new InvalidArgumentException(
                'Priority must be an integer'
            );
        }

        $this->priority = intval($priority);
        return $this;
    }

    /**
     * Retrieve the entity's priority index.
     *
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }
}
