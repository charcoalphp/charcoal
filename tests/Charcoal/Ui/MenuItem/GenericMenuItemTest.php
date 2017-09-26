<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\MenuItem\GenericMenuItem;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;

/**
 *
 */
class GenericMenuItemTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericMenuItem $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $container = $this->getContainer();
        $container->register(new MenuServiceProvider());

        $container['view'] = null;

        $menu = $container['menu/builder']->build([]);

        $this->obj = new GenericMenuItem([
            'view'              => $container['view'],
            'menu'              => $menu,
            'menu_item_builder' => $container['menu/item/builder']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('charcoal/ui/menu-item/generic', $this->obj->type());
    }
}
