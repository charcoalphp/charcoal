<?php

namespace Charcoal\Ui;

/**
 * Defines a UI Grouping.
 */
interface UiGroupingInterface extends UiItemInterface
{
    /**
     * Set the identifier of the sidemenu group.
     *
     * @param  string $ident Sidemenu group identifier.
     * @return UiGroupingInterface Returns the current item.
     */
    public function setIdent($ident);

    /**
     * Retrieve the idenfitier of the sidemenu group.
     *
     * @return string
     */
    public function ident();
}
