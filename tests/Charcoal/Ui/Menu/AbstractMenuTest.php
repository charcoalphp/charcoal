<?php

namespace Charcoal\Tests\Ui\Layout;

// PSR-3 logger test dependencies
use \Psr\Log\NullLogger;

// Pimple test dependencies
use \Pimple\Container;

use \Charcoal\Ui\ServiceProvider\MenuServiceProvider;

class AbstractMenuTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = new Container();
        $container->register(new MenuServiceProvider());

        $container['logger'] = new NullLogger();
        $container['view'] = null;

        $this->obj = $this->getMockForAbstractClass('\Charcoal\Ui\Menu\AbstractMenu', [[
                'view'              => $container['view'],
                'menu_item_builder' => $container['menu/item/builder']
            ]]);
    }

    public function testHasItems()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasItems());

        $ret = $obj->setItems([
            'test'=>[]
        ]);

        $this->assertTrue($obj->hasItems());
    }

    public function testNumItems()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numItems());

        $ret = $obj->setItems([
            'test'=>[],
            'foobar'=>[]
        ]);

         $this->assertEquals(2, $obj->numItems());
    }
}
