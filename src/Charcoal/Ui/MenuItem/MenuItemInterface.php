<?php

namespace Charcoal\Ui\MenuItem;

/**
 *
 */
interface MenuItemInterface
{
    /**
     * @param string $ident
     * @return MenuItemInterface Chainable
     */
    public function setIdent($ident);

    /**
     * @return string
     */
    public function ident();

    /**
     * @param string $label
     * @return MenuItemInterface Chainable
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function label();

    /**
     * @param string $url
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
     * @param array $children
     * @return MenuItemInterface Chainable
     */
    public function setChildren($children);

    /**
     * @param array|MenuItemInterface $child
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
