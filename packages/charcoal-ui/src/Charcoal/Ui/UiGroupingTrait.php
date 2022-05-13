<?php

namespace Charcoal\Ui;

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
}
