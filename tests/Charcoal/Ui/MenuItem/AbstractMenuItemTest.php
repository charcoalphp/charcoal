<?php

namespace Charcoal\Tests\Ui\MenuItem;

use Charcoal\Ui\MenuItem\AbstractMenuItem;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;

class AbstractMenuItemTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();
        $container->register(new MenuServiceProvider());

        $container['view'] = null;

        $menu = $container['menu/builder']->build([]);

        $this->obj = $this->getMockForAbstractClass(AbstractMenuItem::class, [[
            'view'              => $container['view'],
            'menu'              => $menu,
            'menu_item_builder' => $container['menu/item/builder']
        ]]);
    }

    public function testHasChildren()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasChildren());

        $ret = $obj->setChildren([
            'test' => []
        ]);

        $this->assertTrue($obj->hasChildren());
    }

    public function testNumChildren()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numChildren());

        $ret = $obj->setChildren([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $obj->numChildren());
    }
}
