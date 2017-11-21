<?php

namespace Charcoal\Ui;

use InvalidArgumentException;

/**
 * Provides an implementation of {@see \Charcoal\Ui\UiGroupingInterface}.
 */
trait UiGroupingTrait
{
    /**
     * The group's identifier.
     *
     * @var string
     */
    private $ident;

    /**
     * The group's priority.
     *
     * @var integer
     */
    private $priority;

    /**
     * Set the identifier of the group.
     *
     * @param  string $ident The group identifier.
     * @return self
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * Retrieve the idenfitier of the group.
     *
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * Set the group's priority or sorting index.
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
     * Retrieve the group's priority or sorting index.
     *
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }
}
