<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\Menu\GenericMenu;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;

/**
 *
 */
class GenericMenuTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericMenu $obj
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

        $this->obj = new GenericMenu([
            'view'              => $container['view'],
            'menu_item_builder' => $container['menu/item/builder']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('charcoal/ui/menu/generic', $this->obj->type());
    }
}
