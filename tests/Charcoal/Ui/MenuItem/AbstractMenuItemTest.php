<?php

namespace Charcoal\Tests\Ui\MenuItem;

// From 'charcoal-ui'
use Charcoal\Ui\MenuItem\AbstractMenuItem;
use Charcoal\Ui\ServiceProvider\MenuServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractMenuItemTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractMenuItem
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();
        $container->register(new MenuServiceProvider());

        $provider = $this->getContainerProvider();
        $provider->registerView($container);

        $menu = $container['menu/builder']->build([]);

        $this->obj = $this->getMockForAbstractClass(AbstractMenuItem::class, [
            [
                'menu'              => $menu,
                'logger'            => $container['logger'],
                'view'              => $container['view'],
                'menu_item_builder' => $container['menu/item/builder'],
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testHasChildren()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasChildren());

        $ret = $obj->setChildren([
            'test' => []
        ]);

        $this->assertTrue($obj->hasChildren());
    }

    /**
     * @return void
     */
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
