<?php

namespace Charcoal\Ui\MenuItem;

/**
 * Defines a menu item.
 */
interface MenuItemInterface
{
    /**
     * @param string $ident The menu item identifier.
     * @return MenuItemInterface Chainable
     */
    public function setIdent($ident);

    /**
     * @return string
     */
    public function ident();

    /**
     * @param string $label The menu item label.
     * @return MenuItemInterface Chainable
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function label();

    /**
     * @param string $url The menu item URL.
     * @return MenuItemInterface Chainable
     */
    public function setUrl($url);

    /**
     * @return string
     */
    public function url();

    /**
     * @return boolean
     */
    public function hasUrl();

    /**
     * @param array $children The menu item children menu structure.
     * @return MenuItemInterface Chainable
     */
    public function setChildren(array $children);

    /**
     * @param array|MenuItemInterface $child The child object or structure.
     * @return MenuItemInterface Chainable
     */
    public function addChild($child);

    /**
     * @return MenuItemInterface[]
     */
    public function children();

    /**
     * @return boolean
     */
    public function hasChildren();

    /**
     * @return integer
     */
    public function numChildren();
}
