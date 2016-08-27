<?php

namespace Charcoal\Ui\Menu;

use \InvalidArgumentException;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\AbstractUiItem;
use \Charcoal\Ui\Menu\MenuInterface;
use \Charcoal\Ui\MenuItem\MenuItemInterface;
use \Charcoal\Ui\MenuItem\MenuItemBuilder;

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
        $this->setMenuItemBuilder($data['menu_item_builder']);
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
        foreach ($items as $item) {
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * @param array|MenuItemInterface $item A menu item structure or object.
     * @throws InvalidArgumentException If the item argument is not a structure or object.
     * @return MenuItem Chainable
     */
    public function addItem($item)
    {
        if (is_array($item)) {
            $item['menu'] = $this;
            $i = $this->menuItemBuilder->build($item);
            $this->items[] = $i;
        } elseif ($item instanceof MenuItemInterface) {
            $item->setMenu($this);
            $this->items[] = $item;
        } else {
            throw new InvalidArgumentException(
                'Item must be an array of menu item options or a MenuItem object'
            );
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
        uasort($items, ['self', 'sortItemsByPriority']);

        $itemCallback = isset($itemCallback) ? $itemCallback : $this->itemCallback;
        foreach ($items as $item) {
            if ($itemCallback) {
                $itemCallback($item);
            }
            $GLOBALS['widget_template'] = $item->template();
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

    /**
     * Static comparison function used by {@see uasort()}.
     *
     * @param  MenuItemInterface $a Menu A.
     * @param  MenuItemInterface $b Menu B.
     * @return integer Sorting value: -1, 0, or 1
     */
    protected static function sortItemsByPriority(
        MenuItemInterface $a,
        MenuItemInterface $b
    ) {
        $a = $a->priority();
        $b = $b->priority();

        if ($a == $b) {
            return 0;
        }

        return ($a < $b) ? (-1) : 1;
    }
}
