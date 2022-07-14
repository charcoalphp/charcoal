<?php

namespace Charcoal\Ui\Menu;

use InvalidArgumentException;
// From 'charcoal-ui'
use Charcoal\Ui\AbstractUiItem;
use Charcoal\Ui\Menu\MenuInterface;
use Charcoal\Ui\MenuItem\MenuItemInterface;
use Charcoal\Ui\MenuItem\MenuItemBuilder;
use Charcoal\Ui\UiItemInterface;

/**
 * A Basic Menu
 *
 * Abstract implementation of {@see \Charcoal\Ui\Menu\MenuInterface}.
 */
abstract class AbstractMenu extends AbstractUiItem implements
    MenuInterface
{
    /**
     * A collection menu items.
     *
     * @var MenuItemInterface[]
     */
    private $items = [];

    /**
     * A callback applied to each menu item output by {@see self::items()}.
     *
     * @var callable
     */
    private $itemCallback;

    /**
     * Store a menu builder instance.
     *
     * @var MenuItemBuilder $menuItemBuilder
     */
    private $menuItemBuilder;

    /**
     * Return a new menu.
     *
     * @param array|\ArrayAccess $data Class dependencies.
     */
    public function __construct($data)
    {
        parent::__construct($data);

        $this->setMenuItemBuilder($data['menu_item_builder']);

        /** Satisfies {@see \Charcoal\View\ViewableInterface} */
        $this->setView($data['view']);
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
     * @param callable $cb The item callback.
     * @return AbstractMenu Chainable
     */
    public function setItemCallback(callable $cb)
    {
        $this->itemCallback = $cb;
        return $this;
    }

    /**
     * @param array $items The menu items.
     * @return AbstractMenu Chainable
     */
    public function setItems(array $items)
    {
        $this->items = [];
        foreach ($items as $ident => $item) {
            $this->addItem($item, $ident);
        }
        return $this;
    }

    /**
     * @param array|MenuItemInterface $item  A menu item structure or object.
     * @param string                  $ident The menu item identifier, if any.
     * @throws InvalidArgumentException If the item argument is not a structure or object.
     * @return MenuItem Chainable
     */
    public function addItem($item, $ident = null)
    {
        if (is_array($item)) {
            $item['menu'] = $this;
            if (!isset($item['ident'])) {
                $item['ident'] = $ident;
            }
            $i = $this->menuItemBuilder->build($item);
            $item = $i;
        } elseif ($item instanceof MenuItemInterface) {
            if ($item->ident() === null) {
                $item->setIdent($ident);
            }
            $item->setMenu($this);
        } else {
            throw new InvalidArgumentException(
                'Item must be an array of menu item options or a MenuItem object'
            );
        }
        if ($ident === null) {
            $this->items[] = $item;
        } else {
            $this->items[$ident] = $item;
        }
        return $this;
    }

    /**
     * Menu Item generator.
     *
     * @param callable $itemCallback Optional. Item callback.
     * @return MenuItemInterface[]
     */
    public function items(callable $itemCallback = null)
    {
        $items = $this->items;
        uasort($items, [ $this, 'sortItemsByPriority' ]);

        $itemCallback = isset($itemCallback) ? $itemCallback : $this->itemCallback;
        foreach ($items as $item) {
            if ($itemCallback) {
                $itemCallback($item);
            }
            $this->setDynamicTemplate('widget_template', $item->template());
            yield $item->ident() => $item;
        }
    }

    /**
     * @return boolean
     */
    public function hasItems()
    {
        return (count($this->items) > 0);
    }

    /**
     * @return integer
     */
    public function numItems()
    {
        return count($this->items);
    }
}
