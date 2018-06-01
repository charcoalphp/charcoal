<?php

namespace Charcoal\Tests\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\MenuItem\GenericMenuItem;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericMenuItemTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericMenuItem
     */
    public $obj;

    /**
     * @return void
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

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('charcoal/ui/menu-item/generic', $this->obj->type());
    }
}
