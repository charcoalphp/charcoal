<?php

namespace Charcoal\Tests\Ui\MenuItem;

// PSR-3 logger test dependencies
use \Psr\Log\NullLogger;

// Pimple test dependencies
use \Pimple\Container;

use \Charcoal\Ui\ServiceProvider\MenuServiceProvider;

class AbstractMenuItemTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = new Container();
        $container->register(new MenuServiceProvider());

        $container['logger'] = new NullLogger();
        $container['view'] = null;

        $menu = $container['menu/builder']->build([]);

        $this->obj = $this->getMockForAbstractClass('\Charcoal\Ui\MenuItem\AbstractMenuItem', [[
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
            'test'=>[]
        ]);

        $this->assertTrue($obj->hasChildren());
    }

    public function testNumChildren()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numChildren());

        $ret = $obj->setChildren([
            'test'=>[],
            'foobar'=>[]
        ]);

         $this->assertEquals(2, $obj->numChildren());
    }
}
