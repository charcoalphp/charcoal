<?php

namespace Charcoal\Ui;

/**
 * Describes an entity with a priority level or sorting index.
 */
interface PrioritizableInterface
{
    /**
     * Set the entity's priority index.
     *
     * @param  integer $priority An index, for sorting.
     * @return PrioritizableInterface Returns the current instance.
     */
    public function setPriority($priority);

    /**
     * Retrieve the entity's priority index.
     *
     * @return integer
     */
    public function priority();
}
