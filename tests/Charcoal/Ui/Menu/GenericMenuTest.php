<?php

namespace Charcoal\Tests\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\Menu\GenericMenu;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericMenuTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var GenericMenu $obj
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

        $this->obj = new GenericMenu([
            'view'              => $container['view'],
            'menu_item_builder' => $container['menu/item/builder']
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('charcoal/ui/menu/generic', $this->obj->type());
    }
}
