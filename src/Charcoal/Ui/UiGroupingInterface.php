<?php

namespace Charcoal\Ui;

/**
 * Defines a UI Grouping.
 */
interface UiGroupingInterface extends UiItemInterface
{
    /**
     * Set the identifier of the secondary menu group.
     *
     * @param  string $ident Secondary menu group identifier.
     * @return UiGroupingInterface Returns the current item.
     */
    public function setIdent($ident);

    /**
     * Retrieve the idenfitier of the secondary menu group.
     *
     * @return string
     */
    public function ident();
}
