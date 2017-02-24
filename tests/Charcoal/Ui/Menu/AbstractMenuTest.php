<?php

namespace Charcoal\Tests\Ui\Layout;

use Charcoal\Ui\Menu\AbstractMenu;
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
        $this->assertFalse($obj->hasItems());

        $ret = $obj->setItems([
            'test' => []
        ]);

        $this->assertTrue($obj->hasItems());
    }

    public function testNumItems()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numItems());

        $ret = $obj->setItems([
            'test'   => [],
            'foobar' => []
        ]);

         $this->assertEquals(2, $obj->numItems());
    }
}
