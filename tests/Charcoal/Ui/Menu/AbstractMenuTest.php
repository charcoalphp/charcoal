<?php

namespace Charcoal\Tests\Ui\Layout;

use Charcoal\Ui\Menu\AbstractMenu;
use Charcoal\Ui\MenuItem\MenuItemInterface;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;

class AbstractMenuTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();
        $container->register(new MenuServiceProvider());

        $container['view'] = null;

        $this->obj = $this->getMockForAbstractClass(AbstractMenu::class, [[
            'view'              => $container['view'],
            'menu_item_builder' => $container['menu/item/builder']
        ]]);
    }

    public function testHasItems()
    {
        $obj = $this->obj;
        $this->assertFalse($this->obj->hasItems());

        $this->obj->setItems([
            'test' => []
        ]);

        $this->assertTrue($this->obj->hasItems());
    }

    public function testNumItems()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numItems());

        $obj->setItems([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $obj->numItems());
    }

    public function testItems()
    {
        $ret = iterator_to_array($this->obj->items());
        $this->assertEmpty($ret);

        $items = [
            'test'   => [],
            'foobar' => []
        ];
        $this->obj->setItems($items);

        $ret = iterator_to_array($this->obj->items());


        $this->assertEquals(['test', 'foobar'], array_keys($ret));

        $this->assertInstanceOf(MenuItemInterface::class, $ret['test']);
        $this->assertInstanceOf(MenuItemInterface::class, $ret['foobar']);
    }

    public function testItemCallback()
    {
        $cb = function($item) {
            $item['property_from_callback'] = 'yes';
        };
        $ret = $this->obj->setItemCallback($cb);
        $this->assertSame($ret, $this->obj);

        $this->obj->setItems([
            'test'   => [],
            'foobar' => []
        ]);

        $ret = iterator_to_array($this->obj->items());
        $this->assertEquals('yes', $ret['test']['property_from_callback']);
        $this->assertEquals('yes', $ret['foobar']['property_from_callback']);
    }

    public function testItemsPriority()
    {
        $ret = iterator_to_array($this->obj->items());
        $this->assertEmpty($ret);

        $items = [
            'test'   => [
                'priority'=>2
            ],
            'foobar' => [
                'priority'=>1
            ]
        ];
        $this->obj->setItems($items);

        $ret = iterator_to_array($this->obj->items());


        $this->assertEquals(['foobar', 'test'], array_keys($ret));
    }
}
