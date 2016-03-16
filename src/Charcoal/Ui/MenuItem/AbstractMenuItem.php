<?php

namespace Charcoal\Ui\MenuItem;

use \InvalidArgumentException;

// Module `charcoal-translation` dependencies
use \Charcoal\Translation\TranslationString;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\AbstractUiItem;
use \Charcoal\Ui\Menu\MenuInterface;
use \Charcoal\Ui\MenuItem\MenuItemInterface;

/**
 * Default implementation of the MenuInterface, as an abstract class.
 */
abstract class AbstractMenuItem extends AbstractUiItem implements MenuItemInterface
{
    /**
     * Parent menu item
     * @var MenuInterface $menu
     */
    private $menu;

    /**
     * @var string $ident
     */
    protected $ident;

    /**
     * @var TranslationString $label
     */
    protected $label;

    /**
     * @var string $url
     */
    protected $url;

    /**
     * @var MenuItemInterface[] $children
     */
    protected $children;

    /**
     * @var callable $childCallback
     */
    private $childCallback = null;

     /**
      * @param array|ArrayAccess $data Class dependencies.
      */
    public function __construct($data)
    {
        $this->setMenu($data['menu']);
        $this->setMenuItemBuilder($data['menu_item_builder']);
    }

    protected function setMenu(MenuInterface $menu)
    {
        $this->menu = $menu;
        return $this;
    }

    /**
     * @param MenuItemBuilder $menuItemBuilder The Menu Item Builder that will be used to create new items.
     * @return AsbtractMenu Chainable
     */
    public function setMenuItemBuilder(MenuItemBuilder $menuItemBuilder)
    {
        $this->menuItemBuilder = $menuItemBuilder;
        return $this;
    }

    /**
     * @param callable $cb
     * @return AbstractMenu Chainable
     */
    public function setItemCallback(callable $cb)
    {
        $this->childCallback = $cb;
        return $this;
    }

    /**
     * @param string $ident The menu item identifier.
     * @throws InvalidArgumentException
     * @return MenuItem Chainable
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Ident must a string'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param string $label
     * @return MenuItem Chainable
     */
    public function setLabel($label)
    {
        $this->label = new TranslationString($label);
        return $this;
    }

    /**
     * @return string
     */
    public function label()
    {
        return $this->label;
    }

        /**
         * @param string $url
         * @return MenuItem Chainable
         */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function url()
    {
        return $this->url;
    }

    /**
     * @return boolean
     */
    public function hasUrl()
    {
        return !!($this->url());
    }

    /**
     * @param array $children
     * @throws InvalidArgumentException
     * @return MenuItem Chainable
     */
    public function setChildren($children)
    {
        if (!is_array($children)) {
            throw new InvalidArgumentException(
                'Children must be an array'
            );
        }
        $this->children = [];
        foreach ($children as $c) {
            $this->addChild($c);
        }
        return $this;
    }

    /**
     * @param array|MenuItem $child
     * @throws InvalidArgumentException
     * @return MenuItem Chainable
     */
    public function addChild($child)
    {
        if (is_array($child)) {
            $child['menu'] = $this->menu;
            $c = $this->menuItemBuilder->build($child);
            $this->children[] = $c;
        } elseif ($child instanceof MenuItemInterface) {
            $this->children[] = $child;
        } else {
            throw new InvalidArgumentException(
                'Child must be an array or a MenuItem object'
            );
        }
        return $this;
    }

    /**
     * Children (menu item) generator
     *
     * @return MenuItemInterface[]
     */
    public function children($childCallback = nulll)
    {
        $children = $this->children;
        uasort($children, ['self', 'sortChildrenByPrioriy']);

        $childCallback = isset($childCallback) ? $childCallback : $this->childCallback;
        foreach ($children as $child) {
            if ($childCallback) {
                $childCallback($child);
            }
            $GLOBALS['widget_template'] = $item->template();
            yield $child->ident() => $child;
        }
    }

    /**
     * @return boolean
     */
    public function hasChildren()
    {
        return (count($this->children) > 0);
    }

    /**
     * @return integer
     */
    public function numChildren()
    {
        return count($this->children);
    }
}
